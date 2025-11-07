import { SVGAttributes } from "react";

export default function ApplicationLogo(props: SVGAttributes<SVGElement>) {
    return (
        <img
            src="/images/logo.png"
            alt="Logo"
            className="h-9 w-auto"
            {...props}
        />
    );
}
