import { ReactNode } from "react";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Link } from "@inertiajs/react";

interface ActionCardProps {
  title: string;
  description: string;
  icon: ReactNode;
  actionLabel: string;
  actionRoute: string;
  children?: ReactNode;
}

export default function ActionCard({
  title,
  description,
  icon,
  actionLabel,
  actionRoute,
  children
}: ActionCardProps) {
  return (
    <Card className="overflow-hidden">
      <CardHeader className="pb-2">
        <div className="flex items-center justify-between">
          <CardTitle className="text-lg">{title}</CardTitle>
          <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
            {icon}
          </div>
        </div>
        <CardDescription>{description}</CardDescription>
      </CardHeader>
      <CardContent className="pt-2 pb-0">
        <div className="text-sm text-muted-foreground">
          {children}
        </div>
      </CardContent>
      <CardFooter className="pt-4">
        <Button asChild className="w-full">
          <Link href={actionRoute}>
            {actionLabel}
          </Link>
        </Button>
      </CardFooter>
    </Card>
  );
}
