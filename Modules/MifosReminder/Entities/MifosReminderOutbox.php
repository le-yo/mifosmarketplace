<?php

namespace Modules\MifosReminder\Entities;

use Illuminate\Database\Eloquent\Model;

class MifosReminderOutbox extends Model
{
    protected $table ='mifos_reminders_outbox';
    protected $fillable = [];
}
