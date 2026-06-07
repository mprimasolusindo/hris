export type ConsoleLogEntry = {
    level: 'log' | 'info' | 'warn' | 'error';
    timestamp: string;
    message: string;
};

const MAX_ENTRIES = 100;

const buffer: ConsoleLogEntry[] = [];

let initialized = false;

const formatArgs = (args: unknown[]): string =>
    args
        .map((arg) => {
            if (typeof arg === 'string') {
                return arg;
            }

            try {
                return JSON.stringify(arg);
            } catch {
                return String(arg);
            }
        })
        .join(' ');

export function initConsoleCapture(): void {
    if (initialized || typeof window === 'undefined') {
        return;
    }

    initialized = true;

    (['log', 'info', 'warn', 'error'] as const).forEach((level) => {
        const original = console[level].bind(console);

        console[level] = (...args: unknown[]) => {
            buffer.push({
                level,
                timestamp: new Date().toISOString(),
                message: formatArgs(args),
            });

            if (buffer.length > MAX_ENTRIES) {
                buffer.shift();
            }

            original(...args);
        };
    });
}

export function getConsoleBuffer(): ConsoleLogEntry[] {
    return [...buffer];
}
