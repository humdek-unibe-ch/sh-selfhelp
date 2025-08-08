<?php

namespace App\Service\CMS\Common;

final class StyleNames
{
   
    /**
     * Style that is used to display a user input form (read/view context)
     */
    public const STYLE_SHOW_USER_INPUT = 'showUserInput';

     /**
     * Styles that are allowed to be used for submitting data     
     */
    public const FORM_STYLE_NAMES = [
        'formUserInputRecord',
        'formUserInputLog',
    ];
}


