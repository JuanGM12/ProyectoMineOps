<?php
declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
#[Fillable(['id','code','title','description','area','location','responsible_id','priority','status','scheduled_date','due_date'])]
final class ActivityModel extends Model
{
    protected $table = 'activities';
    public $incrementing = false;
    protected $keyType = 'string';
    protected function casts(): array
    {
        return ['scheduled_date' => 'immutable_date', 'due_date' => 'immutable_date'];
    }
}
