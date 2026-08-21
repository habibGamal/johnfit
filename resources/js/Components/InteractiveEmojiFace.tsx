import { motion } from 'framer-motion';

export type ExpressionKey =
    | 'thinking'
    | 'happy'
    | 'excited'
    | 'serious'
    | 'worried'
    | 'cool'
    | 'fire';

export interface FaceExpression {
    browL: string;
    browR: string;
    eyeRy: number;
    pupilCy: number;
    mouth: string;
    blush: number;
    sweat: number;
    stars: number;
    bg: string;
    mood: string;
}

export const EXPRESSIONS: Record<ExpressionKey, FaceExpression> = {
    thinking: {
        browL: 'M22 24 Q28 20 34 24',
        browR: 'M46 24 Q52 20 58 24',
        eyeRy: 8,
        pupilCy: 35,
        mouth: 'M28 57 Q40 57 52 57',
        blush: 0,
        sweat: 0,
        stars: 0,
        bg: '#2a2f3e',
        mood: 'Thinking...',
    },
    happy: {
        browL: 'M22 22 Q28 18 34 22',
        browR: 'M46 22 Q52 18 58 22',
        eyeRy: 7,
        pupilCy: 34,
        mouth: 'M26 55 Q40 68 54 55',
        blush: 0.6,
        sweat: 0,
        stars: 0,
        bg: '#1e2a1e',
        mood: 'Awesome! 🙌',
    },
    excited: {
        browL: 'M22 20 Q28 15 34 20',
        browR: 'M46 20 Q52 15 58 20',
        eyeRy: 9,
        pupilCy: 34,
        mouth: 'M24 54 Q40 70 56 54',
        blush: 0.8,
        sweat: 0,
        stars: 1,
        bg: '#1e2a1e',
        mood: 'Great Choice! 🔥',
    },
    serious: {
        browL: 'M22 26 Q28 23 34 26',
        browR: 'M46 26 Q52 23 58 26',
        eyeRy: 6,
        pupilCy: 36,
        mouth: 'M28 58 Q40 55 52 58',
        blush: 0,
        sweat: 0,
        stars: 0,
        bg: '#1e1e2a',
        mood: 'Got it! 💭',
    },
    worried: {
        browL: 'M22 26 Q28 30 34 26',
        browR: 'M46 26 Q52 30 58 26',
        eyeRy: 8,
        pupilCy: 35,
        mouth: 'M28 60 Q40 55 52 60',
        blush: 0,
        sweat: 0.8,
        stars: 0,
        bg: '#1e1a14',
        mood: "We'll work on this! 💪",
    },
    cool: {
        browL: 'M22 24 Q28 21 34 24',
        browR: 'M46 24 Q52 21 58 24',
        eyeRy: 5,
        pupilCy: 36,
        mouth: 'M28 56 Q40 63 52 56',
        blush: 0.3,
        sweat: 0,
        stars: 0,
        bg: '#1a1a2e',
        mood: 'Sounds Good! 👌',
    },
    fire: {
        browL: 'M21 20 Q28 15 35 20',
        browR: 'M45 20 Q52 15 59 20',
        eyeRy: 9,
        pupilCy: 33,
        mouth: 'M24 54 Q40 72 56 54',
        blush: 0.9,
        sweat: 0,
        stars: 1,
        bg: '#2a1a0e',
        mood: 'Almost There! 🚀',
    },
};

interface InteractiveEmojiFaceProps {
    expressionKey?: ExpressionKey;
    bounceTrigger?: number;
    subtitle?: string;
    className?: string;
}

export default function InteractiveEmojiFace({
    expressionKey = 'thinking',
    bounceTrigger = 0,
    subtitle,
    className = '',
}: InteractiveEmojiFaceProps) {
    const expr = EXPRESSIONS[expressionKey] || EXPRESSIONS.thinking;
    const moodText = subtitle || expr.mood;

    return (
        <div className={`flex flex-col items-center select-none ${className}`}>
            {/* Outer Avatar Container */}
            <motion.div
                animate={{
                    scale: bounceTrigger ? [1, 1.15, 0.95, 1.05, 1] : [1, 1.02, 1],
                    rotate: bounceTrigger ? [0, -6, 6, -3, 0] : 0,
                }}
                key={bounceTrigger}
                transition={{
                    duration: 0.5,
                    ease: 'easeOut',
                }}
                className="relative p-[3px] rounded-full bg-gradient-to-tr from-amber-500 via-orange-500 to-yellow-400 shadow-[0_0_30px_rgba(245,166,35,0.35)]"
            >
                <div className="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-card flex items-center justify-center overflow-hidden border-2 border-background/40">
                    <svg
                        viewBox="0 0 80 80"
                        className="w-20 h-20 sm:w-24 sm:h-24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        {/* Background */}
                        <motion.circle
                            cx="40"
                            cy="40"
                            r="38"
                            animate={{ fill: expr.bg }}
                            transition={{ duration: 0.4 }}
                        />

                        {/* Blush */}
                        <motion.ellipse
                            cx="18"
                            cy="52"
                            rx="8"
                            ry="5"
                            fill="rgba(255,120,80,0.4)"
                            animate={{ opacity: expr.blush }}
                            transition={{ duration: 0.3 }}
                        />
                        <motion.ellipse
                            cx="62"
                            cy="52"
                            rx="8"
                            ry="5"
                            fill="rgba(255,120,80,0.4)"
                            animate={{ opacity: expr.blush }}
                            transition={{ duration: 0.3 }}
                        />

                        {/* Sweat */}
                        <motion.ellipse
                            cx="68"
                            cy="20"
                            rx="4"
                            ry="6"
                            fill="#5bc8f5"
                            animate={{ opacity: expr.sweat }}
                            transition={{ duration: 0.3 }}
                        />

                        {/* Stars */}
                        <motion.text
                            x="8"
                            y="26"
                            fontSize="10"
                            fill="#f5a623"
                            animate={{ opacity: expr.stars }}
                            transition={{ duration: 0.3 }}
                        >
                            ★
                        </motion.text>
                        <motion.text
                            x="62"
                            y="26"
                            fontSize="10"
                            fill="#f5a623"
                            animate={{ opacity: expr.stars }}
                            transition={{ duration: 0.3 }}
                        >
                            ★
                        </motion.text>

                        {/* Eyebrows */}
                        <motion.path
                            stroke="#f0f2f7"
                            strokeWidth="3"
                            strokeLinecap="round"
                            fill="none"
                            animate={{ d: expr.browL }}
                            transition={{ duration: 0.35, ease: 'easeInOut' }}
                        />
                        <motion.path
                            stroke="#f0f2f7"
                            strokeWidth="3"
                            strokeLinecap="round"
                            fill="none"
                            animate={{ d: expr.browR }}
                            transition={{ duration: 0.35, ease: 'easeInOut' }}
                        />

                        {/* Eyes */}
                        <motion.ellipse
                            cx="28"
                            cy="34"
                            rx="8"
                            fill="#f0f2f7"
                            animate={{ ry: expr.eyeRy }}
                            transition={{ duration: 0.35, ease: 'easeInOut' }}
                        />
                        <motion.ellipse
                            cx="52"
                            cy="34"
                            rx="8"
                            fill="#f0f2f7"
                            animate={{ ry: expr.eyeRy }}
                            transition={{ duration: 0.35, ease: 'easeInOut' }}
                        />

                        {/* Pupils */}
                        <motion.circle
                            cx="28"
                            r="4"
                            fill="#0d0f14"
                            animate={{ cy: expr.pupilCy }}
                            transition={{ duration: 0.35, ease: 'easeInOut' }}
                        />
                        <motion.circle
                            cx="52"
                            r="4"
                            fill="#0d0f14"
                            animate={{ cy: expr.pupilCy }}
                            transition={{ duration: 0.35, ease: 'easeInOut' }}
                        />

                        {/* Eye catchlights */}
                        <circle cx="30" cy="32" r="1.5" fill="#ffffff" />
                        <circle cx="54" cy="32" r="1.5" fill="#ffffff" />

                        {/* Mouth */}
                        <motion.path
                            stroke="#f0f2f7"
                            strokeWidth="2.5"
                            strokeLinecap="round"
                            fill="none"
                            animate={{ d: expr.mouth }}
                            transition={{ duration: 0.35, ease: 'easeInOut' }}
                        />
                    </svg>
                </div>
            </motion.div>

            {/* Subtitle / Mood label */}
            <motion.div
                key={moodText}
                initial={{ opacity: 0, y: 4 }}
                animate={{ opacity: 1, y: 0 }}
                className="mt-2 text-sm font-bold text-amber-500 dark:text-amber-400 min-h-[20px] text-center"
            >
                {moodText}
            </motion.div>
        </div>
    );
}
