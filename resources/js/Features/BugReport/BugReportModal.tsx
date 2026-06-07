import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { getConsoleBuffer } from '@/lib/consoleCapture';
import { useLanguage } from '@/i18n/LanguageContext';
import { router } from '@inertiajs/react';
import * as htmlToImage from 'html-to-image';
import { Loader2 } from 'lucide-react';
import { FormEventHandler, useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';
import AnnotationCanvas, { type AnnotationCanvasHandle } from './AnnotationCanvas';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function BugReportModal({ open, onOpenChange }: Props) {
    const { t } = useLanguage();
    const canvasRef = useRef<AnnotationCanvasHandle>(null);
    const [title, setTitle] = useState('');
    const [description, setDescription] = useState('');
    const [imageSrc, setImageSrc] = useState<string | null>(null);
    const [capturing, setCapturing] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [canvasReady, setCanvasReady] = useState(false);

    useEffect(() => {
        if (!open) {
            setTitle('');
            setDescription('');
            setImageSrc(null);
            setCapturing(false);
            setSubmitting(false);
            setCanvasReady(false);
            return;
        }

        let cancelled = false;

        const capture = async () => {
            setCapturing(true);
            setImageSrc(null);
            setCanvasReady(false);

            await new Promise((resolve) => requestAnimationFrame(() => resolve(undefined)));

            try {
                const dataUrl = await htmlToImage.toPng(document.body, {
                    cacheBust: true,
                    filter: (node) => {
                        if (!(node instanceof HTMLElement)) {
                            return true;
                        }

                        return !node.closest('[data-bug-report-ui]');
                    },
                });

                if (!cancelled) {
                    setImageSrc(dataUrl);
                }
            } catch {
                if (!cancelled) {
                    toast.error(t('captureScreenshot'));
                }
            } finally {
                if (!cancelled) {
                    setCapturing(false);
                }
            }
        };

        void capture();

        return () => {
            cancelled = true;
        };
    }, [open, t]);

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();

        if (!title.trim()) {
            toast.error(t('bugReportTitle'));
            return;
        }

        const blob = await canvasRef.current?.exportToBlob();
        if (!blob) {
            toast.error(t('screenshot'));
            return;
        }

        setSubmitting(true);

        const formData = new FormData();
        formData.append('title', title.trim());
        formData.append('description', description.trim());
        formData.append('url', window.location.href);
        formData.append('page_title', document.title);
        formData.append('console_log', JSON.stringify(getConsoleBuffer()));
        formData.append('user_agent', navigator.userAgent);
        formData.append('viewport_width', String(window.innerWidth));
        formData.append('viewport_height', String(window.innerHeight));
        formData.append('screenshot', blob, 'bug-report.png');

        router.post(route('bug-reports.store'), formData, {
            forceFormData: true,
            onSuccess: () => {
                toast.success(t('submitBugReport'));
                onOpenChange(false);
            },
            onError: () => {
                toast.error(t('submitBugReport'));
            },
            onFinish: () => setSubmitting(false),
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-4xl" data-bug-report-ui>
                <DialogHeader>
                    <DialogTitle>{t('bugReport')}</DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    {capturing && (
                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                            <Loader2 className="h-4 w-4 animate-spin" />
                            {t('captureScreenshot')}
                        </div>
                    )}

                    {imageSrc && (
                        <AnnotationCanvas
                            ref={canvasRef}
                            imageSrc={imageSrc}
                            onReadyChange={setCanvasReady}
                        />
                    )}

                    <div className="grid gap-4 md:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="bug-title">{t('bugReportTitle')}</Label>
                            <Input
                                id="bug-title"
                                value={title}
                                onChange={(e) => setTitle(e.target.value)}
                                required
                            />
                        </div>
                        <div className="space-y-2 md:col-span-2">
                            <Label htmlFor="bug-description">{t('bugReportDescription')}</Label>
                            <Textarea
                                id="bug-description"
                                value={description}
                                onChange={(e) => setDescription(e.target.value)}
                                rows={4}
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                            {t('cancel')}
                        </Button>
                        <Button
                            type="submit"
                            disabled={submitting || capturing || !canvasReady}
                        >
                            {submitting ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    {t('submitBugReport')}
                                </>
                            ) : (
                                t('submitBugReport')
                            )}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
