<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectBrief extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'project_briefs';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_name', 'description', 'category_id', 'img_info', 'start_date', 'end_date', 'status', 'company_id'
    ];
}
