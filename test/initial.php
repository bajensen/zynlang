<?php
require '../vendor/autoload.php';

echo \ZynLang\Html::a()
    ->title('Google')
    ->href('https://www.google.com/')
    ->html('Go to Google');

echo \ZynLang\Html::a()
    // id
    ->attr('id', 'test')
    // href
    ->attr('href', 'https://google.com/?q=hi')
    // class
    ->attr('class', 'btn btn-success')
    ->attr('class', ['btn ', 'btn-success'])
    ->attr('class', ['btn btn-success'])
    // style
    ->attr('style', 'text-align: right')
    ->attr('style', 'text-align: right;')
    ->attr('style', 'text-align: right; font-size: 2em')
    ->attr('style', ['text-align: right; font-size: 2em'])
    ->attr('style', ['text-align: right; font-size: 2em', 'font-weight: 200'])
    ->attr('style', ['text-align: right', 'font-size: 2em'])
    ->attr('style', ['text-align' => 'right'])
    ->attr('style', ['text-align' => 'right', 'font-size' => '1.5em'])
    ->delAttrVal('style', ['text-align'])
    // boolean attributes
    ->attr('disabled')
    // content
    ->html('This is a test.')
;
