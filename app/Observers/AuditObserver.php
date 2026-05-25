<?php

namespace App\Observers;

use Illuminate\Support\Facades\DB;

class AuditObserver
{
    private function log($action, $model)
    {
        if (!auth()->check()) {
            return;
        }
        
        $table = $model->getTable();
        $recordId = $model->getKey();
        
        // Log to database
        DB::table('audit_logs')->insert([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $recordId,
            'old_values' => $action === 'updated' || $action === 'deleted' ? json_encode($model->getOriginal()) : null,
            'new_values' => $action === 'created' || $action === 'updated' ? json_encode($model->getAttributes()) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function created($model): void
    {
        $this->log('created', $model);
    }

    public function updated($model): void
    {
        $this->log('updated', $model);
    }

    public function deleted($model): void
    {
        $this->log('deleted', $model);
    }
}
