# InBody Logs System

The InBody Logs system provides comprehensive body composition tracking and analysis for users. It allows users to record their measurements (weight, muscle mass, body fat, etc.) and provides intelligent feedback on their progress.

## Overview

The system consists of a Laravel backend for data storage and analysis, and a React (Inertia.js) frontend for visualization.

### Key Components

- **Model**: `App\Models\InBodyLog` - Stores raw measurement data.
- **Service**: `App\Services\InBodyAnalysisService` - Handles complex calculations, delta tracking, and body composition classification.
- **Controller**: `App\Http\Controllers\InBodyLogController` - Manages CRUD operations and passes analysis data to the frontend.
- **Frontend**: `resources/js/Pages/InBody/Index.tsx` - Dashboard for viewing stats, charts, and history.

---

## Data Model (`inbody_logs`)

| Field | Type | Description | Unit |
|-------|------|-------------|------|
| `weight` | Decimal | Total body weight | kg |
| `smm` | Decimal | Skeletal Muscle Mass | kg |
| `pbf` | Decimal | Percent Body Fat | % |
| `bmi` | Decimal | Body Mass Index | - |
| `bmr` | Decimal | Basal Metabolic Rate | kcal |
| `body_water` | Decimal | Total Body Water | L |
| `lean_body_mass`| Decimal | Lean Body Mass | kg |
| `visceral_fat` | Decimal | Visceral Fat Level | - |
| `measured_at` | Date | Date of the measurement | - |

---

## Analysis Logic

The `InBodyAnalysisService` calculates several metrics to provide feedback:

### 1. Delta Calculation
Compares the latest measurement with the previous one to calculate:
- Absolute change
- Percentage change
- Weekly rate of change
- Trend direction (Up/Down/Stable)

### 2. Body Composition Classification
Based on changes in **SMM (Skeletal Muscle Mass)** and **PBF (Percent Body Fat)**, the system classifies progress into 8 categories:

| Classification | Meaning | Status |
|----------------|---------|--------|
| **Recomposition** | Gaining muscle AND losing fat | Excellent |
| **Lean Bulk** | Gaining muscle with minimal fat gain | Positive |
| **Cutting** | Losing fat while maintaining muscle | Positive |
| **Bulk** | Gaining both muscle and some fat | Neutral |
| **Maintenance** | Stable body composition | Neutral |
| **Aggressive Cut** | Quick weight loss, but losing muscle too | Negative |
| **Fat Gain** | Increasing fat without muscle gain | Negative |
| **Muscle Loss** | Decreasing muscle mass | Negative |

---

## Frontend Integration

The dashboard (`InBody/Index.tsx`) receives a comprehensive `analysis` object containing:

```typescript
export interface InBodyAnalysis {
    latest: InBodyLog | null;      // Latest measurement
    previous: InBodyLog | null;    // Previous measurement for comparison
    delta: InBodyDelta | null;     // Calculated changes
    bodyCompositionAnalysis: InBodyAnalysisResult | null; // Classification & Score
    trends: InBodyTrends;          // Long-term trends
    statistics: InBodyStatistics | null; // Min/Max/Avg stats
    history: InBodyHistoryEntry[]; // Data for charts
}
```

### Visual Components
- **Metric Cards**: Display core stats with trend indicators.
- **Body Composition Chart**: Visualizes weight, SMM, and PBF over time.
- **Analysis Card**: Provides qualitative feedback and recommendations.
- **History Timeline**: Chronological list of all measurements.

---

## Usage Example

### Backend Header
```php
$analysis = $analysisService->getAnalysis($user);
```

### Frontend Type Safety
```tsx
import { InBodyAnalysis } from '@/types';

const InBodyIndex = ({ analysis }: { analysis: InBodyAnalysis }) => {
    // ...
}
```

---

## Configuration & Multi-tenancy
The system supports multi-tenancy via the `tenant_id` field. Scoping is handled by the `scopeForTenant` method in the `InBodyLog` model.
