import { Button } from '@/Components/ui/button';
import { useLanguage } from '@/i18n/LanguageContext';
import {
    forwardRef,
    useCallback,
    useEffect,
    useImperativeHandle,
    useRef,
    useState,
} from 'react';

type Point = { x: number; y: number };

type DrawAction =
    | { type: 'pen'; points: Point[] }
    | { type: 'rectangle'; start: Point; end: Point };

type AnnotationTool = 'rectangle' | 'pen';

type Props = {
    imageSrc: string;
    onReadyChange?: (ready: boolean) => void;
};

export type AnnotationCanvasHandle = {
    exportToBlob: () => Promise<Blob | null>;
};

const STROKE_COLOR = '#ef4444';
const STROKE_WIDTH = 3;

const AnnotationCanvas = forwardRef<AnnotationCanvasHandle, Props>(function AnnotationCanvas(
    { imageSrc, onReadyChange },
    ref,
) {
    const { t } = useLanguage();
    const canvasRef = useRef<HTMLCanvasElement>(null);
    const imageRef = useRef<HTMLImageElement | null>(null);
    const [tool, setTool] = useState<AnnotationTool>('pen');
    const [actions, setActions] = useState<DrawAction[]>([]);
    const [draft, setDraft] = useState<DrawAction | null>(null);
    const [isDrawing, setIsDrawing] = useState(false);

    const getPoint = (event: React.MouseEvent<HTMLCanvasElement>): Point => {
        const canvas = canvasRef.current;
        if (!canvas) {
            return { x: 0, y: 0 };
        }

        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;

        return {
            x: (event.clientX - rect.left) * scaleX,
            y: (event.clientY - rect.top) * scaleY,
        };
    };

    const drawActions = useCallback((ctx: CanvasRenderingContext2D, items: DrawAction[]) => {
        ctx.strokeStyle = STROKE_COLOR;
        ctx.lineWidth = STROKE_WIDTH;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        items.forEach((action) => {
            if (action.type === 'pen') {
                if (action.points.length < 2) {
                    return;
                }
                ctx.beginPath();
                ctx.moveTo(action.points[0].x, action.points[0].y);
                action.points.slice(1).forEach((point) => ctx.lineTo(point.x, point.y));
                ctx.stroke();
                return;
            }

            const x = Math.min(action.start.x, action.end.x);
            const y = Math.min(action.start.y, action.end.y);
            const width = Math.abs(action.end.x - action.start.x);
            const height = Math.abs(action.end.y - action.start.y);
            ctx.strokeRect(x, y, width, height);
        });
    }, []);

    const render = useCallback(() => {
        const canvas = canvasRef.current;
        const image = imageRef.current;
        if (!canvas || !image) {
            return;
        }

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return;
        }

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
        drawActions(ctx, actions);
        if (draft) {
            drawActions(ctx, [draft]);
        }
    }, [actions, draft, drawActions]);

    useEffect(() => {
        const image = new Image();
        image.onload = () => {
            imageRef.current = image;
            const canvas = canvasRef.current;
            if (!canvas) {
                return;
            }

            canvas.width = image.width;
            canvas.height = image.height;
            render();
            onReadyChange?.(true);
        };
        image.src = imageSrc;
    }, [imageSrc, onReadyChange, render]);

    useEffect(() => {
        render();
    }, [render]);

    useImperativeHandle(ref, () => ({
        exportToBlob: () =>
            new Promise((resolve) => {
                canvasRef.current?.toBlob((blob) => resolve(blob), 'image/png');
            }),
    }));

    const handleMouseDown = (event: React.MouseEvent<HTMLCanvasElement>) => {
        const point = getPoint(event);
        setIsDrawing(true);

        if (tool === 'pen') {
            setDraft({ type: 'pen', points: [point] });
            return;
        }

        setDraft({ type: 'rectangle', start: point, end: point });
    };

    const handleMouseMove = (event: React.MouseEvent<HTMLCanvasElement>) => {
        if (!isDrawing || !draft) {
            return;
        }

        const point = getPoint(event);

        if (draft.type === 'pen') {
            setDraft({ ...draft, points: [...draft.points, point] });
            return;
        }

        setDraft({ ...draft, end: point });
    };

    const finishDrawing = () => {
        if (!draft) {
            setIsDrawing(false);
            return;
        }

        setActions((current) => [...current, draft]);
        setDraft(null);
        setIsDrawing(false);
    };

    const clearAnnotations = () => {
        setActions([]);
        setDraft(null);
    };

    return (
        <div className="space-y-3">
            <div className="flex flex-wrap gap-2">
                <Button
                    type="button"
                    size="sm"
                    variant={tool === 'rectangle' ? 'default' : 'outline'}
                    onClick={() => setTool('rectangle')}
                >
                    {t('drawRectangle')}
                </Button>
                <Button
                    type="button"
                    size="sm"
                    variant={tool === 'pen' ? 'default' : 'outline'}
                    onClick={() => setTool('pen')}
                >
                    {t('drawPen')}
                </Button>
                <Button type="button" size="sm" variant="outline" onClick={clearAnnotations}>
                    {t('clearAnnotations')}
                </Button>
            </div>
            <div className="max-h-[420px] overflow-auto rounded-md border bg-muted/20">
                <canvas
                    ref={canvasRef}
                    className="max-w-full cursor-crosshair"
                    onMouseDown={handleMouseDown}
                    onMouseMove={handleMouseMove}
                    onMouseUp={finishDrawing}
                    onMouseLeave={finishDrawing}
                />
            </div>
        </div>
    );
});

export default AnnotationCanvas;
