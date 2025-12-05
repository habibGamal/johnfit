import { Head, Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { cn } from "@/lib/utils";

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: "",
        email: "",
        password: "",
        password_confirmation: "",
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route("register"), {
            onFinish: () => reset("password", "password_confirmation"),
        });
    };

    const handleGoogleSignup = () => {
        window.location.href = route("auth.google");
    };

    return (
        <div className="w-full lg:grid lg:min-h-screen lg:grid-cols-2">
            <Head title="Register" />

            {/* Left Side - Image & Branding */}
            <div className="relative hidden h-full flex-col bg-muted p-10 text-white dark:border-r lg:flex">
                <div className="absolute inset-0 bg-zinc-900" />
                <div
                    className="absolute inset-0 bg-cover bg-center opacity-50"
                    style={{ backgroundImage: "url('/images/intro.jpg')" }}
                />
                <div className="relative z-20 flex items-center text-lg font-medium">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        className="mr-2 h-6 w-6"
                    >
                        <path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3" />
                    </svg>
                    JohnFit
                </div>
                <div className="relative z-20 mt-auto">
                    <blockquote className="space-y-2">
                        <p className="text-lg">
                            &ldquo;Success starts with self-discipline. Create your account and start your journey.&rdquo;
                        </p>
                        <footer className="text-sm">John Doe</footer>
                    </blockquote>
                </div>
            </div>

            {/* Right Side - Register Form */}
            <div className="flex items-center justify-center py-12">
                <div className="mx-auto grid w-[350px] gap-6">
                    <div className="grid gap-2 text-center">
                        <h1 className="text-3xl font-bold">Create an account</h1>
                        <p className="text-balance text-muted-foreground">
                            Enter your information to create an account
                        </p>
                    </div>

                    <form onSubmit={submit} className="grid gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="name">Name</Label>
                            <Input
                                id="name"
                                placeholder="John Doe"
                                required
                                value={data.name}
                                onChange={(e) => setData("name", e.target.value)}
                                className={cn(errors.name && "border-red-500 focus-visible:ring-red-500")}
                            />
                            {errors.name && (
                                <p className="text-sm text-red-500">{errors.name}</p>
                            )}
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                placeholder="m@example.com"
                                required
                                value={data.email}
                                onChange={(e) => setData("email", e.target.value)}
                                className={cn(errors.email && "border-red-500 focus-visible:ring-red-500")}
                            />
                            {errors.email && (
                                <p className="text-sm text-red-500">{errors.email}</p>
                            )}
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                required
                                value={data.password}
                                onChange={(e) => setData("password", e.target.value)}
                                className={cn(errors.password && "border-red-500 focus-visible:ring-red-500")}
                            />
                            {errors.password && (
                                <p className="text-sm text-red-500">{errors.password}</p>
                            )}
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">Confirm Password</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                required
                                value={data.password_confirmation}
                                onChange={(e) => setData("password_confirmation", e.target.value)}
                                className={cn(errors.password_confirmation && "border-red-500 focus-visible:ring-red-500")}
                            />
                            {errors.password_confirmation && (
                                <p className="text-sm text-red-500">{errors.password_confirmation}</p>
                            )}
                        </div>

                        <Button type="submit" className="w-full" disabled={processing}>
                            Create account
                        </Button>

                        <Button variant="outline" type="button" className="w-full" onClick={handleGoogleSignup}>
                            <svg
                                className="mr-2 h-4 w-4"
                                aria-hidden="true"
                                focusable="false"
                                data-prefix="fab"
                                data-icon="google"
                                role="img"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 488 512"
                            >
                                <path
                                    fill="currentColor"
                                    d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h240z"
                                />
                            </svg>
                            Sign up with Google
                        </Button>
                    </form>
                    <div className="mt-4 text-center text-sm">
                        Already have an account?{" "}
                        <Link href={route("login")} className="underline">
                            Login
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
