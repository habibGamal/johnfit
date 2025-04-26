import { ReactNode } from "react";
import { Button } from "@/Components/ui/button";
import { cn } from "@/lib/utils";

interface EmptyStateProps {
    title: string;
    description: string;
    icon?: ReactNode;
    action?: {
        label: string;
        href?: string;
        onClick?: () => void;
        icon?: ReactNode;
    };
    className?: string;
}

export default function EmptyState({
    title,
    description,
    icon,
    action,
    className
}: EmptyStateProps) {
    return (
        <div className={cn(
            "flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted p-12 text-center",
            className
        )}>
            {icon && (
                <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
                    {icon}
                </div>
            )}

            <h3 className="mt-6 text-lg font-medium text-foreground">{title}</h3>

            <p className="mt-2 text-sm text-muted-foreground max-w-sm">
                {description}
            </p>

            {action && (
                <Button
                    className="mt-6"
                    variant="outline"
                    onClick={action.onClick}
                    {...(action.href ? { asChild: true } : {})}
                >
                    {action.href ? (
                        <a href={action.href}>
                            {action.icon && <span className="mr-2">{action.icon}</span>}
                            {action.label}
                        </a>
                    ) : (
                        <>
                            {action.icon && <span className="mr-2">{action.icon}</span>}
                            {action.label}
                        </>
                    )}
                </Button>
            )}
        </div>
    );
}
