import { ReactNode } from "react";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Link } from "@inertiajs/react";
import { ArrowRight } from "lucide-react";

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
    <Card className="overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-1 border-gray-100 dark:border-gray-700 dark:bg-gray-800/50 backdrop-blur-sm group">
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between mb-2">
          <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700/50 group-hover:bg-primary/10 transition-colors duration-300">
            {icon}
          </div>
        </div>
        <CardTitle className="text-lg font-bold text-gray-900 dark:text-white group-hover:text-primary transition-colors duration-300">{title}</CardTitle>
        <CardDescription className="text-gray-500 dark:text-gray-400 line-clamp-2">{description}</CardDescription>
      </CardHeader>
      <CardContent className="pt-0 pb-4 min-h-[80px]">
        <div className="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
          {children}
        </div>
      </CardContent>
      <CardFooter className="pt-0 pb-6">
        <Button asChild className="w-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:bg-primary hover:text-white dark:hover:bg-primary dark:hover:text-white transition-all duration-300 group-hover:shadow-md">
          <Link href={actionRoute} className="flex items-center justify-center gap-2">
            {actionLabel}
            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </Button>
      </CardFooter>
    </Card>
  );
}
