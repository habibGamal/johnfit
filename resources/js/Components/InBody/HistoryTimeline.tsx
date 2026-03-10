import { motion } from 'framer-motion';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { History, Scale, Activity, Percent } from 'lucide-react';
import { InBodyHistoryEntry } from '@/types';
import { cn } from '@/lib/utils';

interface HistoryTimelineProps {
  history: InBodyHistoryEntry[];
}

export default function HistoryTimeline({ history }: HistoryTimelineProps) {
  const recentHistory = history.slice(-10).reverse();

  if (recentHistory.length === 0) {
    return (
      <Card className="border-border bg-card">
        <CardContent className="flex items-center justify-center h-48">
          <div className="text-center">
            <History className="h-12 w-12 text-muted-foreground mx-auto mb-3" />
            <p className="text-muted-foreground">No measurements yet</p>
            <p className="text-sm text-muted-foreground">
              Start tracking your InBody data
            </p>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, delay: 0.5 }}
    >
      <Card className="border-border bg-card">
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-lg font-bold text-foreground">
            <div className="p-2 bg-teal-500/10 rounded-lg">
              <History className="h-5 w-5 text-teal-600" />
            </div>
            Recent Measurements
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="relative">
            {/* Timeline line */}
            <div className="absolute left-4 top-0 bottom-0 w-0.5 bg-border" />

            <div className="space-y-4">
              {recentHistory.map((entry, index) => (
                <motion.div
                  key={entry.fullDate}
                  className="relative pl-10"
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.6 + index * 0.1 }}
                >
                  {/* Timeline dot */}
                  <div className={cn(
                    'absolute left-2 w-4 h-4 rounded-full border-2 border-background',
                    index === 0
                      ? 'bg-primary ring-4 ring-primary/20'
                      : 'bg-muted-foreground'
                  )} />

                  <div className={cn(
                    'p-4 rounded-xl transition-all duration-200',
                    index === 0
                      ? 'bg-primary/10 border border-primary/20'
                      : 'bg-muted/30 hover:bg-muted/50'
                  )}>
                    <div className="flex items-center justify-between mb-2">
                      <span className={cn(
                        'text-sm font-semibold',
                        index === 0
                          ? 'text-primary'
                          : 'text-muted-foreground'
                      )}>
                        {entry.date}
                      </span>
                      {index === 0 && (
                        <span className="px-2 py-0.5 text-xs font-medium bg-primary text-primary-foreground rounded-full">
                          Latest
                        </span>
                      )}
                    </div>

                    <div className="grid grid-cols-3 gap-3">
                      <div className="flex items-center gap-2">
                        <Scale className="h-4 w-4 text-blue-500" />
                        <div>
                          <p className="text-xs text-muted-foreground">Weight</p>
                          <p className="text-sm font-semibold text-foreground">
                            {entry.weight} kg
                          </p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Activity className="h-4 w-4 text-emerald-500" />
                        <div>
                          <p className="text-xs text-muted-foreground">SMM</p>
                          <p className="text-sm font-semibold text-foreground">
                            {entry.smm} kg
                          </p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Percent className="h-4 w-4 text-orange-500" />
                        <div>
                          <p className="text-xs text-muted-foreground">Body Fat</p>
                          <p className="text-sm font-semibold text-foreground">
                            {entry.pbf}%
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </motion.div>
              ))}
            </div>
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
}
