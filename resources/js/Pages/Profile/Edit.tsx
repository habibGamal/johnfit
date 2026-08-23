import React, { useState, useRef, FormEventHandler } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { PageProps } from '@/types';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/Components/ui/alert-dialog';
import {
    User as UserIcon,
    Mail,
    Lock,
    KeyRound,
    ShieldCheck,
    ShieldAlert,
    Trash2,
    CheckCircle2,
    AlertCircle,
    Loader2,
    Sparkles,
    Eye,
    EyeOff,
    Save,
    Dumbbell,
    Check,
} from 'lucide-react';

interface ProfileEditProps {
    mustVerifyEmail: boolean;
    status?: string;
}

export default function Edit({ mustVerifyEmail, status }: ProfileEditProps) {
    const user = usePage<PageProps>().props.auth.user;
    const [activeTab, setActiveTab] = useState('general');

    // Initials for avatar
    const initials = user?.name
        ? user.name
            .split(' ')
            .map((n) => n[0])
            .join('')
            .toUpperCase()
            .substring(0, 2)
        : 'FI';

    // ----------------------------------------------------
    // Profile Information Form Logic
    // ----------------------------------------------------
    const {
        data: profileData,
        setData: setProfileData,
        patch: updateProfile,
        errors: profileErrors,
        processing: profileProcessing,
        recentlySuccessful: profileSuccess,
    } = useForm({
        name: user.name,
        email: user.email,
    });

    const submitProfile: FormEventHandler = (e) => {
        e.preventDefault();
        updateProfile(route('profile.update'));
    };

    // ----------------------------------------------------
    // Password Update Form Logic
    // ----------------------------------------------------
    const passwordInputRef = useRef<HTMLInputElement>(null);
    const currentPasswordInputRef = useRef<HTMLInputElement>(null);
    const [showCurrentPassword, setShowCurrentPassword] = useState(false);
    const [showNewPassword, setShowNewPassword] = useState(false);

    const {
        data: passwordData,
        setData: setPasswordData,
        put: updatePassword,
        errors: passwordErrors,
        processing: passwordProcessing,
        recentlySuccessful: passwordSuccess,
        reset: resetPasswordForm,
    } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submitPassword: FormEventHandler = (e) => {
        e.preventDefault();
        updatePassword(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => resetPasswordForm(),
            onError: (errs) => {
                if (errs.password) {
                    resetPasswordForm('password', 'password_confirmation');
                    passwordInputRef.current?.focus();
                }
                if (errs.current_password) {
                    resetPasswordForm('current_password');
                    currentPasswordInputRef.current?.focus();
                }
            },
        });
    };

    // ----------------------------------------------------
    // Delete Account Form Logic
    // ----------------------------------------------------
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const deletePasswordInputRef = useRef<HTMLInputElement>(null);

    const {
        data: deleteData,
        setData: setDeleteData,
        delete: destroyUser,
        processing: deleteProcessing,
        reset: resetDeleteForm,
        errors: deleteErrors,
        clearErrors: clearDeleteErrors,
    } = useForm({
        password: '',
    });

    const submitDeleteUser: FormEventHandler = (e) => {
        e.preventDefault();
        destroyUser(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => setIsDeleteDialogOpen(false),
            onError: () => deletePasswordInputRef.current?.focus(),
            onFinish: () => resetDeleteForm(),
        });
    };

    const handleCloseDeleteDialog = () => {
        setIsDeleteDialogOpen(false);
        clearDeleteErrors();
        resetDeleteForm();
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2">
                            <h2 className="text-2xl font-black uppercase tracking-wider text-foreground">
                                Account Profile
                            </h2>
                            <Sparkles className="w-5 h-5 text-yellow-500" />
                        </div>
                        <p className="text-sm text-muted-foreground mt-1">
                            Manage your personal details, credentials, and account settings
                        </p>
                    </div>

                    <div className="flex items-center gap-2 px-3 py-1.5 rounded-full bg-yellow-500/10 border border-yellow-500/20 w-fit">
                        <Dumbbell className="w-4 h-4 text-yellow-500" />
                        <span className="text-xs font-bold text-yellow-500 uppercase tracking-wider">
                            JohnFit Member
                        </span>
                    </div>
                </div>
            }
        >
            <Head title="Profile Settings" />

            <div className="py-10 relative min-h-[calc(100vh-10rem)]">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 relative z-10 space-y-8">
                    {/* User Banner Card */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.4 }}
                    >
                        <Card className="border-yellow-500/20 bg-card/60 backdrop-blur-md overflow-hidden shadow-xl">
                            <div className="p-6 sm:p-8 flex flex-col sm:flex-row items-center gap-6">
                                <div className="relative">
                                    <div className="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-primary flex items-center justify-center text-zinc-950 font-black text-2xl sm:text-3xl shadow-md border-2 border-yellow-400/40">
                                        {initials}
                                    </div>
                                    <div className="absolute -bottom-1 -right-1 p-1.5 rounded-full bg-background border border-border shadow">
                                        {user.email_verified_at ? (
                                            <ShieldCheck className="w-4 h-4 text-emerald-500" />
                                        ) : (
                                            <ShieldAlert className="w-4 h-4 text-amber-500" />
                                        )}
                                    </div>
                                </div>

                                <div className="flex-1 text-center sm:text-left space-y-2">
                                    <div className="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                                        <h3 className="text-2xl font-bold text-foreground tracking-tight">
                                            {user.name}
                                        </h3>
                                        <Badge
                                            variant="secondary"
                                            className="w-fit mx-auto sm:mx-0 bg-yellow-500/10 text-yellow-500 border-yellow-500/20 hover:bg-yellow-500/20"
                                        >
                                            Athlete Profile
                                        </Badge>
                                    </div>

                                    <div className="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-sm text-muted-foreground">
                                        <span className="flex items-center gap-1.5">
                                            <Mail className="w-4 h-4 text-yellow-500/80" />
                                            {user.email}
                                        </span>
                                        <span className="inline-block w-1 h-1 rounded-full bg-muted-foreground/40" />
                                        <span>
                                            Status:{' '}
                                            {user.email_verified_at ? (
                                                <span className="text-emerald-500 font-semibold">Verified</span>
                                            ) : (
                                                <span className="text-amber-500 font-semibold">Unverified</span>
                                            )}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </Card>
                    </motion.div>

                    {/* Main Tabs Section */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.4, delay: 0.1 }}
                    >
                        <Tabs defaultValue="general" value={activeTab} onValueChange={setActiveTab} className="space-y-6">
                            <TabsList className="grid grid-cols-3 w-full max-w-md mx-auto sm:mx-0 bg-secondary/60 p-1 rounded-xl border border-border/50">
                                <TabsTrigger
                                    value="general"
                                    className="data-[state=active]:bg-background data-[state=active]:text-yellow-500 data-[state=active]:shadow-sm rounded-lg font-medium text-xs sm:text-sm flex items-center justify-center gap-2 py-1.5 transition-all"
                                >
                                    <UserIcon className="w-4 h-4" />
                                    <span>General</span>
                                </TabsTrigger>
                                <TabsTrigger
                                    value="security"
                                    className="data-[state=active]:bg-background data-[state=active]:text-yellow-500 data-[state=active]:shadow-sm rounded-lg font-medium text-xs sm:text-sm flex items-center justify-center gap-2 py-1.5 transition-all"
                                >
                                    <KeyRound className="w-4 h-4" />
                                    <span>Security</span>
                                </TabsTrigger>
                                <TabsTrigger
                                    value="danger"
                                    className="data-[state=active]:bg-background data-[state=active]:text-destructive data-[state=active]:shadow-sm rounded-lg font-medium text-xs sm:text-sm flex items-center justify-center gap-2 py-1.5 transition-all"
                                >
                                    <Trash2 className="w-4 h-4" />
                                    <span>Danger</span>
                                </TabsTrigger>
                            </TabsList>

                            {/* Tab 1: General Info */}
                            <TabsContent value="general">
                                <Card className="border-border/60 bg-card/80 backdrop-blur-md shadow-lg">
                                    <CardHeader className="border-b border-border/40 pb-5">
                                        <div className="flex items-center gap-3">
                                            <div className="p-2.5 rounded-xl bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">
                                                <UserIcon className="w-5 h-5" />
                                            </div>
                                            <div>
                                                <CardTitle className="text-xl font-bold">Profile Information</CardTitle>
                                                <CardDescription className="text-muted-foreground mt-0.5">
                                                    Update your account's profile name and registered email address.
                                                </CardDescription>
                                            </div>
                                        </div>
                                    </CardHeader>

                                    <CardContent className="pt-6">
                                        <form onSubmit={submitProfile} className="space-y-6 max-w-2xl">
                                            <div className="space-y-2">
                                                <Label htmlFor="name" className="text-sm font-semibold flex items-center gap-2">
                                                    <UserIcon className="w-3.5 h-3.5 text-yellow-500" />
                                                    Full Name
                                                </Label>
                                                <Input
                                                    id="name"
                                                    type="text"
                                                    value={profileData.name}
                                                    onChange={(e) => setProfileData('name', e.target.value)}
                                                    required
                                                    autoComplete="name"
                                                    placeholder="Enter your name"
                                                    className="bg-secondary/40 border-border/80 focus:border-yellow-500 h-11"
                                                />
                                                {profileErrors.name && (
                                                    <p className="text-xs text-destructive font-medium flex items-center gap-1.5 mt-1">
                                                        <AlertCircle className="w-3.5 h-3.5" />
                                                        {profileErrors.name}
                                                    </p>
                                                )}
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="email" className="text-sm font-semibold flex items-center gap-2">
                                                    <Mail className="w-3.5 h-3.5 text-yellow-500" />
                                                    Email Address
                                                </Label>
                                                <Input
                                                    id="email"
                                                    type="email"
                                                    value={profileData.email}
                                                    onChange={(e) => setProfileData('email', e.target.value)}
                                                    required
                                                    autoComplete="username"
                                                    placeholder="Enter your email"
                                                    className="bg-secondary/40 border-border/80 focus:border-yellow-500 h-11"
                                                />
                                                {profileErrors.email && (
                                                    <p className="text-xs text-destructive font-medium flex items-center gap-1.5 mt-1">
                                                        <AlertCircle className="w-3.5 h-3.5" />
                                                        {profileErrors.email}
                                                    </p>
                                                )}
                                            </div>

                                            {mustVerifyEmail && user.email_verified_at === null && (
                                                <div className="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-500 text-sm space-y-2">
                                                    <div className="flex items-center gap-2 font-semibold">
                                                        <AlertCircle className="w-4 h-4" />
                                                        <span>Email address unverified</span>
                                                    </div>
                                                    <p className="text-xs text-amber-500/90 leading-relaxed">
                                                        Your email address requires verification to access all platform features.
                                                    </p>
                                                    <Link
                                                        href={route('verification.send')}
                                                        method="post"
                                                        as="button"
                                                        className="inline-flex items-center gap-1.5 text-xs font-bold underline hover:text-amber-400 transition-colors"
                                                    >
                                                        Resend Verification Email
                                                    </Link>

                                                    {status === 'verification-link-sent' && (
                                                        <p className="text-xs font-medium text-emerald-500 mt-2 flex items-center gap-1">
                                                            <CheckCircle2 className="w-3.5 h-3.5" />
                                                            A new verification link has been sent to your email.
                                                        </p>
                                                    )}
                                                </div>
                                            )}

                                            <div className="flex items-center gap-4 pt-2">
                                                <Button
                                                    type="submit"
                                                    disabled={profileProcessing}
                                                    className="bg-yellow-500 hover:bg-yellow-400 text-zinc-950 font-bold px-6 h-11 rounded-xl shadow-sm transition-all flex items-center gap-2"
                                                >
                                                    {profileProcessing ? (
                                                        <>
                                                            <Loader2 className="w-4 h-4 animate-spin" />
                                                            <span>Saving...</span>
                                                        </>
                                                    ) : (
                                                        <>
                                                            <Save className="w-4 h-4" />
                                                            <span>Save Changes</span>
                                                        </>
                                                    )}
                                                </Button>

                                                <AnimatePresence>
                                                    {profileSuccess && (
                                                        <motion.div
                                                            initial={{ opacity: 0, x: -10 }}
                                                            animate={{ opacity: 1, x: 0 }}
                                                            exit={{ opacity: 0 }}
                                                            className="flex items-center gap-1.5 text-sm font-medium text-emerald-500"
                                                        >
                                                            <Check className="w-4 h-4" />
                                                            <span>Profile updated successfully!</span>
                                                        </motion.div>
                                                    )}
                                                </AnimatePresence>
                                            </div>
                                        </form>
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            {/* Tab 2: Password & Security */}
                            <TabsContent value="security">
                                <Card className="border-border/60 bg-card/80 backdrop-blur-md shadow-lg">
                                    <CardHeader className="border-b border-border/40 pb-5">
                                        <div className="flex items-center gap-3">
                                            <div className="p-2.5 rounded-xl bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">
                                                <Lock className="w-5 h-5" />
                                            </div>
                                            <div>
                                                <CardTitle className="text-xl font-bold">Update Password</CardTitle>
                                                <CardDescription className="text-muted-foreground mt-0.5">
                                                    Ensure your account is using a strong, unique password to maintain security.
                                                </CardDescription>
                                            </div>
                                        </div>
                                    </CardHeader>

                                    <CardContent className="pt-6">
                                        <form onSubmit={submitPassword} className="space-y-6 max-w-2xl">
                                            <div className="space-y-2">
                                                <Label htmlFor="current_password" className="text-sm font-semibold flex items-center gap-2">
                                                    <Lock className="w-3.5 h-3.5 text-yellow-500" />
                                                    Current Password
                                                </Label>
                                                <div className="relative">
                                                    <Input
                                                        id="current_password"
                                                        ref={currentPasswordInputRef}
                                                        type={showCurrentPassword ? 'text' : 'password'}
                                                        value={passwordData.current_password}
                                                        onChange={(e) => setPasswordData('current_password', e.target.value)}
                                                        autoComplete="current-password"
                                                        placeholder="Enter current password"
                                                        className="bg-secondary/40 border-border/80 focus:border-yellow-500 h-11 pr-10"
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => setShowCurrentPassword(!showCurrentPassword)}
                                                        className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors p-1"
                                                    >
                                                        {showCurrentPassword ? (
                                                            <EyeOff className="w-4 h-4" />
                                                        ) : (
                                                            <Eye className="w-4 h-4" />
                                                        )}
                                                    </button>
                                                </div>
                                                {passwordErrors.current_password && (
                                                    <p className="text-xs text-destructive font-medium flex items-center gap-1.5 mt-1">
                                                        <AlertCircle className="w-3.5 h-3.5" />
                                                        {passwordErrors.current_password}
                                                    </p>
                                                )}
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="password" className="text-sm font-semibold flex items-center gap-2">
                                                    <KeyRound className="w-3.5 h-3.5 text-yellow-500" />
                                                    New Password
                                                </Label>
                                                <div className="relative">
                                                    <Input
                                                        id="password"
                                                        ref={passwordInputRef}
                                                        type={showNewPassword ? 'text' : 'password'}
                                                        value={passwordData.password}
                                                        onChange={(e) => setPasswordData('password', e.target.value)}
                                                        autoComplete="new-password"
                                                        placeholder="Enter new password"
                                                        className="bg-secondary/40 border-border/80 focus:border-yellow-500 h-11 pr-10"
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => setShowNewPassword(!showNewPassword)}
                                                        className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors p-1"
                                                    >
                                                        {showNewPassword ? (
                                                            <EyeOff className="w-4 h-4" />
                                                        ) : (
                                                            <Eye className="w-4 h-4" />
                                                        )}
                                                    </button>
                                                </div>
                                                {passwordErrors.password && (
                                                    <p className="text-xs text-destructive font-medium flex items-center gap-1.5 mt-1">
                                                        <AlertCircle className="w-3.5 h-3.5" />
                                                        {passwordErrors.password}
                                                    </p>
                                                )}
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="password_confirmation" className="text-sm font-semibold flex items-center gap-2">
                                                    <CheckCircle2 className="w-3.5 h-3.5 text-yellow-500" />
                                                    Confirm New Password
                                                </Label>
                                                <Input
                                                    id="password_confirmation"
                                                    type="password"
                                                    value={passwordData.password_confirmation}
                                                    onChange={(e) => setPasswordData('password_confirmation', e.target.value)}
                                                    autoComplete="new-password"
                                                    placeholder="Confirm new password"
                                                    className="bg-secondary/40 border-border/80 focus:border-yellow-500 h-11"
                                                />
                                                {passwordErrors.password_confirmation && (
                                                    <p className="text-xs text-destructive font-medium flex items-center gap-1.5 mt-1">
                                                        <AlertCircle className="w-3.5 h-3.5" />
                                                        {passwordErrors.password_confirmation}
                                                    </p>
                                                )}
                                            </div>

                                            <div className="flex items-center gap-4 pt-2">
                                                <Button
                                                    type="submit"
                                                    disabled={passwordProcessing}
                                                    className="bg-yellow-500 hover:bg-yellow-400 text-zinc-950 font-bold px-6 h-11 rounded-xl shadow-sm transition-all flex items-center gap-2"
                                                >
                                                    {passwordProcessing ? (
                                                        <>
                                                            <Loader2 className="w-4 h-4 animate-spin" />
                                                            <span>Updating Password...</span>
                                                        </>
                                                    ) : (
                                                        <>
                                                            <Save className="w-4 h-4" />
                                                            <span>Update Password</span>
                                                        </>
                                                    )}
                                                </Button>

                                                <AnimatePresence>
                                                    {passwordSuccess && (
                                                        <motion.div
                                                            initial={{ opacity: 0, x: -10 }}
                                                            animate={{ opacity: 1, x: 0 }}
                                                            exit={{ opacity: 0 }}
                                                            className="flex items-center gap-1.5 text-sm font-medium text-emerald-500"
                                                        >
                                                            <Check className="w-4 h-4" />
                                                            <span>Password updated successfully!</span>
                                                        </motion.div>
                                                    )}
                                                </AnimatePresence>
                                            </div>
                                        </form>
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            {/* Tab 3: Danger Zone */}
                            <TabsContent value="danger">
                                <Card className="border-destructive/30 bg-destructive/5 backdrop-blur-md shadow-lg">
                                    <CardHeader className="border-b border-destructive/20 pb-5">
                                        <div className="flex items-center gap-3">
                                            <div className="p-2.5 rounded-xl bg-destructive/10 text-destructive border border-destructive/20">
                                                <Trash2 className="w-5 h-5" />
                                            </div>
                                            <div>
                                                <CardTitle className="text-xl font-bold text-destructive">Delete Account</CardTitle>
                                                <CardDescription className="text-muted-foreground mt-0.5">
                                                    Permanently remove your account and all associated data from JohnFit.
                                                </CardDescription>
                                            </div>
                                        </div>
                                    </CardHeader>

                                    <CardContent className="pt-6 space-y-6">
                                        <div className="p-4 rounded-xl bg-destructive/10 border border-destructive/20 text-sm text-foreground space-y-2">
                                            <p className="font-semibold text-destructive flex items-center gap-2">
                                                <AlertCircle className="w-4 h-4" />
                                                Warning: This action is permanent and irreversible
                                            </p>
                                            <p className="text-muted-foreground leading-relaxed text-xs sm:text-sm">
                                                Once your account is deleted, all of its workouts, meal plans, InBody tracking records, and subscription history will be purged immediately.
                                            </p>
                                        </div>

                                        <AlertDialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
                                            <AlertDialogTrigger asChild>
                                                <Button
                                                    variant="destructive"
                                                    className="font-bold px-6 h-11 rounded-xl shadow-md transition-all flex items-center gap-2"
                                                >
                                                    <Trash2 className="w-4 h-4" />
                                                    <span>Delete Account</span>
                                                </Button>
                                            </AlertDialogTrigger>

                                            <AlertDialogContent className="bg-card border-border sm:max-w-md">
                                                <AlertDialogHeader>
                                                    <div className="flex items-center gap-3 mb-2">
                                                        <div className="p-2.5 rounded-xl bg-destructive/10 text-destructive">
                                                            <Trash2 className="w-6 h-6" />
                                                        </div>
                                                        <AlertDialogTitle className="text-lg font-bold">
                                                            Confirm Account Deletion
                                                        </AlertDialogTitle>
                                                    </div>
                                                    <AlertDialogDescription className="text-sm text-muted-foreground leading-relaxed">
                                                        Are you sure you want to delete your account? Please enter your current password to confirm you would like to permanently remove your profile.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>

                                                <form onSubmit={submitDeleteUser} className="space-y-4 py-2">
                                                    <div className="space-y-2">
                                                        <Label htmlFor="delete_password" className="text-sm font-semibold">
                                                            Confirm Password
                                                        </Label>
                                                        <Input
                                                            id="delete_password"
                                                            ref={deletePasswordInputRef}
                                                            type="password"
                                                            value={deleteData.password}
                                                            onChange={(e) => setDeleteData('password', e.target.value)}
                                                            required
                                                            placeholder="Enter password to confirm"
                                                            className="bg-secondary/40 border-border focus:border-destructive h-11"
                                                        />
                                                        {deleteErrors.password && (
                                                            <p className="text-xs text-destructive font-medium flex items-center gap-1.5 mt-1">
                                                                <AlertCircle className="w-3.5 h-3.5" />
                                                                {deleteErrors.password}
                                                            </p>
                                                        )}
                                                    </div>

                                                    <AlertDialogFooter className="pt-2">
                                                        <AlertDialogCancel
                                                            type="button"
                                                            onClick={handleCloseDeleteDialog}
                                                            className="rounded-xl h-10 border-border hover:bg-secondary"
                                                        >
                                                            Cancel
                                                        </AlertDialogCancel>
                                                        <Button
                                                            type="submit"
                                                            variant="destructive"
                                                            disabled={deleteProcessing}
                                                            className="rounded-xl h-10 font-bold flex items-center gap-2"
                                                        >
                                                            {deleteProcessing ? (
                                                                <>
                                                                    <Loader2 className="w-4 h-4 animate-spin" />
                                                                    <span>Deleting...</span>
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <Trash2 className="w-4 h-4" />
                                                                    <span>Permanently Delete</span>
                                                                </>
                                                            )}
                                                        </Button>
                                                    </AlertDialogFooter>
                                                </form>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </CardContent>
                                </Card>
                            </TabsContent>
                        </Tabs>
                    </motion.div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
