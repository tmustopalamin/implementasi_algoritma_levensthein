<?php

namespace App\Models;

use CodeIgniter\Model;

class WordModel extends Model
{
    protected $table = 'typo_indonesia';

    public function getWords()
    {
        return $this->findAll();
    }
}