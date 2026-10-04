<?php

declare(strict_types=1);

return [
    'encoding' => 'UTF-8',
    'finalize' => true,
    'ignoreNonStrings' => false,
    'cachePath' => storage_path('app/purifier'),
    'cacheFileMode' => 0755,
    'settings' => [
        'default' => [
            'HTML.Doctype' => 'HTML 5',
            'HTML.Allowed' => 'p,h2,h3,h4,h5,br,strong,b,em,i,u,s,ul,ol,li,blockquote,figure,img[src|alt|width|height|class],a[href|title|target],div[class],span[class|style],pre,code',
            'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'URI.DisableExternalResources' => 'true',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty' => true,
        ],
        'rich_text' => [
            'HTML.Doctype' => 'HTML 5',
            'HTML.Allowed' => 'p,h2,h3,h4,h5,br,strong,b,em,i,u,s,ul,ol,li,blockquote,figure,img[src|alt|width|height|class|data-upload-id],a[href|title|target],div[class],span[class|style],pre,code',
            'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'URI.DisableExternalResources' => 'true',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty' => true,
        ],
    ],
];
