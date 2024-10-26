<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';
    protected $fillable = [
        'message_person',
        'data',
        'conversation_name',
    ];


    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_name', 'conversation_name');
    }
}
