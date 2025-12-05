import ApplicationLogo from "@/Components/ApplicationLogo";
import { Link } from "@inertiajs/react";
import { PropsWithChildren } from "react";

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="container flex min-h-screen gap-8 flex-col items-center justify-center">
            <div>
                <Link href="/">
                    <ApplicationLogo className="h-20 fill-current text-gray-500" />
                </Link>
            </div>

            {children}
        </div>
    );
}
