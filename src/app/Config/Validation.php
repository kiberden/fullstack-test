<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var string[]
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------

    public array $comment = [
        'user_id' => [
            'label' => 'Пользователь',
            'rules' => 'required|is_natural_no_zero',
            'errors' => [
                'required'           => 'Не указан пользователь',
                'is_natural_no_zero' => 'Некорректный пользователь',
            ],
        ],
        'text' => [
            'label' => 'Текст',
            'rules' => 'required',
            'errors' => [
                'required' => 'Введите текст комментария',
            ],
        ],
    ];

    public array $user_identify = [
        'email' => [
            'label' => 'Email',
            'rules' => 'required|max_length[255]',
            'errors' => [
                'required'   => 'Укажите email',
                'max_length' => 'Email слишком длинный',
            ],
        ],
    ];
}
