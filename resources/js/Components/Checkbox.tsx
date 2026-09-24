import { InputHTMLAttributes } from 'react';

export default function Checkbox({
    className = '',
    ...props
}: InputHTMLAttributes<HTMLInputElement>) {
    return (
        <input
            {...props}
            type="checkbox"
            className={
                'rounded border border-slate-400/70 bg-white/80 text-primary shadow-sm focus:ring-2 focus:ring-ring focus:ring-offset-2 ' +
                className
            }
        />
    );
}
