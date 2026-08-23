import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { UserDailyItem, ItemMealOption } from '@/types';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Badge } from '@/Components/ui/badge';
import { Utensils, CheckCircle2, Flame } from 'lucide-react';

interface MealLoggerModalProps {
    item: UserDailyItem | null;
    isOpen: boolean;
    isLocked: boolean;
    onClose: () => void;
}

export default function MealLoggerModal({
    item,
    isOpen,
    isLocked,
    onClose,
}: MealLoggerModalProps) {
    const [selectedOptionId, setSelectedOptionId] = useState<number | null>(null);
    const [quantity, setQuantity] = useState<number>(100);
    const [isSaving, setIsSaving] = useState(false);

    const options = (item?.target_details?.options || []) as ItemMealOption[];

    useEffect(() => {
        if (!item) return;

        const consumedId = item.execution_payload?.consumed_option_id;
        const defaultId = consumedId || item.reference_id || (options[0]?.meal_id ?? null);
        setSelectedOptionId(defaultId);

        const consumedQty = item.execution_payload?.consumed_quantity;
        const defaultQty = consumedQty || (options[0]?.quantity ?? 100);
        setQuantity(defaultQty);
    }, [item, isOpen]);

    if (!item) return null;

    const selectedOption = options.find((opt) => opt.meal_id === selectedOptionId) || options[0];

    const handleSave = () => {
        if (isLocked || !selectedOptionId) return;
        setIsSaving(true);

        router.post(
            route('schedule.items.meal-consumption', item.id),
            {
                option_id: selectedOptionId,
                quantity: quantity,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    setIsSaving(false);
                    onClose();
                },
                onError: () => {
                    setIsSaving(false);
                },
            }
        );
    };

    // Calculate dynamic macros based on quantity
    const baseQty = selectedOption?.quantity || 100;
    const ratio = baseQty > 0 ? quantity / baseQty : 1;
    const dynamicCalories = Math.round((selectedOption?.calories || 0) * ratio);
    const dynamicProtein = Math.round((selectedOption?.protein || 0) * ratio * 10) / 10;
    const dynamicCarbs = Math.round((selectedOption?.carbs || 0) * ratio * 10) / 10;
    const dynamicFat = Math.round((selectedOption?.fat || 0) * ratio * 10) / 10;

    return (
        <Dialog open={isOpen} onOpenChange={(open) => !open && onClose()}>
            <DialogContent className="sm:max-w-[460px] p-0 overflow-hidden bg-card border-border">
                <DialogHeader className="p-5 pb-3 border-b border-border">
                    <div className="flex items-start justify-between gap-3">
                        <div className="space-y-1">
                            <div className="flex items-center gap-2">
                                <Utensils className="h-5 w-5 text-emerald-500" />
                                <DialogTitle className="text-lg font-bold text-foreground">
                                    {selectedOption?.name || item.item_name}
                                </DialogTitle>
                            </div>
                            <DialogDescription className="text-xs text-muted-foreground">
                                Time Slot: {item.target_details?.time_slot || 'Meal Time'}
                            </DialogDescription>
                        </div>
                        <Badge variant="secondary" className="text-xs font-semibold shrink-0">
                            +{item.points} pts
                        </Badge>
                    </div>
                </DialogHeader>

                <div className="p-5 space-y-4 max-h-[60vh] overflow-y-auto">
                    {/* Option Selector if multiple options */}
                    {options.length > 1 && (
                        <div className="space-y-2">
                            <Label className="text-xs font-semibold text-muted-foreground">
                                Select Consumed Option:
                            </Label>
                            <div className="grid grid-cols-1 gap-2">
                                {options.map((opt) => (
                                    <button
                                        key={opt.meal_id}
                                        type="button"
                                        disabled={isLocked}
                                        onClick={() => {
                                            setSelectedOptionId(opt.meal_id);
                                            setQuantity(opt.quantity || 100);
                                        }}
                                        className={`flex items-center justify-between p-3 rounded-lg border text-left transition-all ${
                                            selectedOptionId === opt.meal_id
                                                ? 'bg-primary/10 border-primary text-foreground font-semibold'
                                                : 'bg-secondary/40 border-border hover:bg-secondary/80 text-muted-foreground'
                                        }`}
                                    >
                                        <span className="text-sm font-medium">{opt.name}</span>
                                        <span className="text-xs font-semibold">{opt.calories} kcal</span>
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Quantity Input */}
                    <div className="space-y-2">
                        <Label htmlFor="quantity" className="text-xs font-semibold text-muted-foreground">
                            Consumed Quantity (grams / ml):
                        </Label>
                        <div className="flex items-center gap-2">
                            <Input
                                id="quantity"
                                type="number"
                                min="1"
                                disabled={isLocked}
                                value={quantity}
                                onChange={(e) => setQuantity(parseFloat(e.target.value) || 0)}
                                className="h-10 text-base font-semibold bg-background"
                            />
                            <span className="text-xs font-bold text-muted-foreground px-2">g</span>
                        </div>
                    </div>

                    {/* Dynamic Macro Summary Card */}
                    <div className="bg-secondary/40 border border-border/80 rounded-xl p-3.5 space-y-2">
                        <div className="flex items-center justify-between text-xs text-muted-foreground font-medium">
                            <span className="flex items-center gap-1">
                                <Flame className="h-3.5 w-3.5 text-orange-500" /> Calories
                            </span>
                            <span className="text-sm font-bold text-foreground">{dynamicCalories} kcal</span>
                        </div>
                        <div className="grid grid-cols-3 gap-2 pt-2 border-t border-border/60 text-center">
                            <div className="p-1.5 rounded-lg bg-blue-500/10">
                                <span className="block text-[11px] text-muted-foreground">Protein</span>
                                <span className="block text-xs font-bold text-blue-500">{dynamicProtein}g</span>
                            </div>
                            <div className="p-1.5 rounded-lg bg-emerald-500/10">
                                <span className="block text-[11px] text-muted-foreground">Carbs</span>
                                <span className="block text-xs font-bold text-emerald-500">{dynamicCarbs}g</span>
                            </div>
                            <div className="p-1.5 rounded-lg bg-amber-500/10">
                                <span className="block text-[11px] text-muted-foreground">Fat</span>
                                <span className="block text-xs font-bold text-amber-500">{dynamicFat}g</span>
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter className="p-4 bg-secondary/30 border-t border-border flex flex-row items-center justify-between sm:justify-between gap-2">
                    <Button variant="ghost" size="sm" onClick={onClose}>
                        Cancel
                    </Button>
                    {!isLocked && (
                        <Button
                            onClick={handleSave}
                            disabled={isSaving}
                            className="gap-1.5 font-semibold bg-emerald-600 hover:bg-emerald-700 text-white"
                        >
                            <CheckCircle2 className="h-4 w-4" />
                            {isSaving ? 'Logging...' : 'Log Meal & Complete'}
                        </Button>
                    )}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
