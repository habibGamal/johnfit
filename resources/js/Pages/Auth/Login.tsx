import { Head, Link, useForm } from "@inertiajs/react";
import { FormEventHandler, useEffect, useState } from "react";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { cn } from "@/lib/utils";
import { ArrowLeft, Mail, Lock, Eye, EyeOff, Loader2 } from "lucide-react";

declare global {
    interface Window {
        pushToken: string | null;
    }
}

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm<{
        email: string;
        password: string;
        remember: boolean;
        expo_token: string | null;
    }>({
        email: "",
        password: "",
        remember: false,
        expo_token: null,
    });

    useEffect(() => {
        setData("expo_token", window.pushToken);
    }, []);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route("login"), {
            onFinish: () => reset("password"),
        });
    };

    const handleGoogleLogin = () => {
        window.location.href = route("auth.google");
    };

    return (
        <div className="min-h-[100dvh] w-full bg-background text-foreground lg:grid lg:min-h-screen lg:grid-cols-2">
            <Head title="Log in" />

            {/* Left Side (Desktop Only) - Hero Image & Official Branding */}
            <div className="relative hidden h-full flex-col justify-between overflow-hidden bg-zinc-950 p-10 text-white lg:flex border-r border-border/40">
                <div
                    className="absolute inset-0 bg-cover bg-center opacity-40 mix-blend-luminosity scale-105"
                    style={{ backgroundImage: "url('/images/intro.jpg')" }}
                />
                <div className="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/70 to-zinc-950/80" />

                {/* Desktop Top Brand Header */}
                <div className="relative z-20 flex items-center justify-between">
                    <Link href="/" className="group flex items-center gap-3">
                        <div className="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-950/90 border border-primary/30 p-2 shadow-xl shadow-amber-500/10 backdrop-blur-md transition-transform duration-300 group-hover:scale-105">
                            <img
                                src="/images/logo.png"
                                alt="JohnFit Logo"
                                className="h-full w-full object-contain"
                            />
                        </div>
                        <div>
                            <span className="block text-2xl font-black tracking-wider text-white">
                                JOHNFIT<span className="text-primary">.</span>
                            </span>
                            <span className="block text-[11px] font-bold uppercase tracking-widest text-primary">
                                Fitness & Nutrition
                            </span>
                        </div>
                    </Link>

                    <Link
                        href="/"
                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-400 hover:text-white transition-colors px-3 py-1.5 rounded-lg bg-zinc-900/60 border border-zinc-800"
                    >
                        <ArrowLeft className="h-3.5 w-3.5" />
                        Back to Home
                    </Link>
                </div>

                {/* Desktop Motivational Quote */}
                <div className="relative z-20 mt-auto">
                    <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold tracking-wider uppercase bg-primary/10 text-primary border border-primary/20 mb-4">
                        <span className="h-1.5 w-1.5 rounded-full bg-primary animate-pulse" />
                        Daily Motivation
                    </div>
                    <blockquote className="space-y-2 max-w-md">
                        <p className="text-xl font-medium leading-relaxed text-zinc-200">
                            &ldquo;The only bad workout is the one that didn&apos;t happen. Consistency is the bridge between goals and accomplishment.&rdquo;
                        </p>
                        <footer className="text-sm font-semibold text-primary">
                            John Doe &bull; Head Coach
                        </footer>
                    </blockquote>
                </div>
            </div>

            {/* Right Side - Login Form with Elevated Mobile Layout */}
            <div className="flex min-h-[100dvh] flex-col justify-center px-4 py-8 sm:px-6 sm:py-12 lg:px-12">
                <div className="mx-auto w-full max-w-sm sm:max-w-md">
                    {/* Top Mobile Bar: Back Link & Portal Badge */}
                    <div className="mb-6 flex items-center justify-between lg:hidden">
                        <Link
                            href="/"
                            className="inline-flex items-center gap-1.5 text-xs font-medium text-muted-foreground hover:text-foreground transition-colors px-3 py-1.5 rounded-lg bg-secondary/40 border border-border/60"
                        >
                            <ArrowLeft className="h-3.5 w-3.5" />
                            <span>Home</span>
                        </Link>
                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold tracking-wider uppercase bg-primary/10 text-primary border border-primary/20">
                            <span className="h-1.5 w-1.5 rounded-full bg-primary animate-pulse" />
                            Athlete Portal
                        </span>
                    </div>

                    {/* Mobile Hero: Official JohnFit Logo & Title */}
                    <div className="mb-6 flex flex-col items-center text-center lg:hidden">
                        <Link href="/" className="group relative mb-3">
                            <div className="absolute -inset-1 rounded-2xl bg-gradient-to-r from-amber-500/25 to-yellow-500/25 blur-sm transition-all duration-300 group-hover:blur-md" />
                            <div className="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-zinc-950 border border-primary/35 p-2 shadow-2xl">
                                <img
                                    src="/images/logo.png"
                                    alt="JohnFit Logo"
                                    className="h-full w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                />
                            </div>
                        </Link>
                        <h1 className="text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                            WELCOME BACK<span className="text-primary">.</span>
                        </h1>
                        <p className="mt-1 text-xs text-muted-foreground sm:text-sm max-w-[300px]">
                            Log in to track workouts, check meal plans, and crush your goals
                        </p>
                    </div>

                    {/* Desktop-only Header */}
                    <div className="hidden lg:block mb-6">
                        <h1 className="text-3xl font-black tracking-tight text-foreground">
                            Sign In to JohnFit<span className="text-primary">.</span>
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Enter your credentials below to access your account
                        </p>
                    </div>

                    {/* Segmented Auth Mode Switcher */}
                    <div className="mb-6 flex rounded-xl bg-secondary/50 p-1 border border-border/70">
                        <div className="flex-1 py-2 text-center text-xs sm:text-sm font-bold rounded-lg bg-primary text-primary-foreground shadow-sm">
                            Sign In
                        </div>
                        <Link
                            href={route("register")}
                            className="flex-1 py-2 text-center text-xs sm:text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
                        >
                            Create Account
                        </Link>
                    </div>

                    {/* Session Status Notification */}
                    {status && (
                        <div className="mb-6 rounded-xl border border-green-500/30 bg-green-500/10 p-3.5 text-xs sm:text-sm font-medium text-green-400">
                            {status}
                        </div>
                    )}

                    {/* Form */}
                    <form onSubmit={submit} className="space-y-4">
                        {/* Email Input */}
                        <div className="space-y-1.5">
                            <Label htmlFor="email" className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Email Address
                            </Label>
                            <div className="relative">
                                <Mail className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/70 pointer-events-none" />
                                <Input
                                    id="email"
                                    type="email"
                                    placeholder="athlete@example.com"
                                    autoComplete="username"
                                    required
                                    value={data.email}
                                    onChange={(e) => setData("email", e.target.value)}
                                    className={cn(
                                        "h-12 pl-10 rounded-xl bg-secondary/30 border-input/80 text-foreground placeholder:text-muted-foreground/60 focus-visible:ring-primary focus-visible:border-primary text-base sm:text-sm transition-all",
                                        errors.email && "border-red-500 focus-visible:ring-red-500"
                                    )}
                                />
                            </div>
                            {errors.email && (
                                <p className="text-xs font-medium text-red-500">{errors.email}</p>
                            )}
                        </div>

                        {/* Password Input */}
                        <div className="space-y-1.5">
                            <div className="flex items-center justify-between">
                                <Label htmlFor="password" className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                    Password
                                </Label>
                                {canResetPassword && (
                                    <Link
                                        href={route("password.request")}
                                        className="text-xs font-semibold text-primary hover:text-primary/80 transition-colors"
                                    >
                                        Forgot password?
                                    </Link>
                                )}
                            </div>
                            <div className="relative">
                                <Lock className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/70 pointer-events-none" />
                                <Input
                                    id="password"
                                    type={showPassword ? "text" : "password"}
                                    placeholder="••••••••"
                                    autoComplete="current-password"
                                    required
                                    value={data.password}
                                    onChange={(e) => setData("password", e.target.value)}
                                    className={cn(
                                        "h-12 pl-10 pr-11 rounded-xl bg-secondary/30 border-input/80 text-foreground placeholder:text-muted-foreground/60 focus-visible:ring-primary focus-visible:border-primary text-base sm:text-sm transition-all",
                                        errors.password && "border-red-500 focus-visible:ring-red-500"
                                    )}
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    className="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground p-1 transition-colors rounded-md focus:outline-none"
                                    aria-label={showPassword ? "Hide password" : "Show password"}
                                >
                                    {showPassword ? (
                                        <EyeOff className="h-4 w-4" />
                                    ) : (
                                        <Eye className="h-4 w-4" />
                                    )}
                                </button>
                            </div>
                            {errors.password && (
                                <p className="text-xs font-medium text-red-500">{errors.password}</p>
                            )}
                        </div>

                        {/* Remember Me Toggle */}
                        <div className="pt-1">
                            <label className="inline-flex items-center gap-2.5 cursor-pointer select-none py-1">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    checked={data.remember}
                                    onChange={(e) => setData("remember", e.target.checked)}
                                    className="h-4 w-4 rounded border-border bg-secondary/40 text-primary focus:ring-primary focus:ring-offset-0 cursor-pointer"
                                />
                                <span className="text-xs sm:text-sm text-muted-foreground hover:text-foreground transition-colors">
                                    Remember my session
                                </span>
                            </label>
                        </div>

                        {/* Submit Button */}
                        <Button
                            type="submit"
                            className="h-12 w-full rounded-xl text-base font-bold bg-primary text-primary-foreground hover:bg-primary/90 shadow-lg shadow-amber-500/15 active:scale-[0.98] transition-all flex items-center justify-center gap-2"
                            disabled={processing}
                        >
                            {processing ? (
                                <>
                                    <Loader2 className="h-5 w-5 animate-spin" />
                                    <span>Signing in...</span>
                                </>
                            ) : (
                                <span>Sign In</span>
                            )}
                        </Button>

                        {/* Divider */}
                        <div className="relative py-2">
                            <div className="absolute inset-0 flex items-center">
                                <span className="w-full border-t border-border/80" />
                            </div>
                            <div className="relative flex justify-center text-[11px] uppercase">
                                <span className="bg-background px-3 font-semibold tracking-wider text-muted-foreground">
                                    or continue with
                                </span>
                            </div>
                        </div>

                        {/* Google Sign-in */}
                        <Button
                            variant="outline"
                            type="button"
                            className="h-12 w-full rounded-xl border-input/80 bg-secondary/30 hover:bg-secondary/60 text-foreground font-medium flex items-center justify-center gap-3 active:scale-[0.98] transition-all"
                            onClick={handleGoogleLogin}
                        >
                            <svg
                                className="h-4 w-4"
                                aria-hidden="true"
                                focusable="false"
                                viewBox="0 0 488 512"
                            >
                                <path
                                    fill="currentColor"
                                    d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h240z"
                                />
                            </svg>
                            <span>Sign in with Google</span>
                        </Button>
                    </form>

                    {/* Bottom Switch Link */}
                    <div className="mt-8 text-center text-xs sm:text-sm text-muted-foreground">
                        Don&apos;t have an account yet?{" "}
                        <Link
                            href={route("register")}
                            className="font-semibold text-primary underline underline-offset-4 hover:text-primary/80 transition-colors"
                        >
                            Create an account
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
