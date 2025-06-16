<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class form_element_value extends Model
{
      protected $fillable = ['form_content_id', 'form_element_id', 'value'];

    public function formContent()
    {
        return $this->belongsTo(FormContent::class);
    }

    public function formElement()
    {
        return $this->belongsTo(FormElement::class);
    }
}
