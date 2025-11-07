<?php

namespace Webkul\Task\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Task\Contracts\TaskActivity as TaskActivityContract;

class TaskActivity extends Model implements TaskActivityContract
{
    /**
     * Tên bảng trong DB.
     *
     * @var string
     */
    protected $table = 'task_activities';

    /**
     * Các trường cho phép gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'task_id',
        'activity_id',
    ];

    /**
     * Liên kết với Task.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Liên kết với Activity.
     */
    public function activity()
    {
        return $this->belongsTo(\Webkul\Activity\Models\Activity::class);
    }
}
