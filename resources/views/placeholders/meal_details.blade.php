<div class="flex gap-4">
    <div class="flex-1">
        <div class="flex gap-2">
            <div class="flex-1">
                <div class="text-lg font-bold text-gray-800">Calories</div>
                <div class="text-sm text-gray-500">{{ $meal->meal_macros['calories'] * $quantity }} kcal</div>
            </div>
            <div class="flex-1">
                <div class="text-lg font-bold text-gray-800">Protein</div>
                <div class="text-sm text-gray-500">{{ $meal->meal_macros['proteins']  * $quantity}} g</div>
            </div>
            <div class="flex-1">
                <div class="text-lg font-bold text-gray-800">Carbs</div>
                <div class="text-sm text-gray-500">{{ $meal->meal_macros['carbs']  * $quantity}} g</div>
            </div>
            <div class="flex-1">
                <div class="text-lg font-bold text-gray-800">Fats</div>
                <div class="text-sm text-gray-500">{{ $meal->meal_macros['fats'] * $quantity }} g</div>
            </div>

        </div>
    </div>

</div>
