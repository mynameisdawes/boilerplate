<?php

return [
    // If null, app()->getLocale() is used.
    'language' => null,

    'initialEditType' => \Datomatic\NovaMarkdownTui\Enums\EditorType::MARKDOWN,

    'previewStyle' => \Datomatic\NovaMarkdownTui\Enums\PreviewStyle::TAB,

    'height' => 'auto',
    'minHeight' => '200px',

    'useCommandShortcut' => true,
    'usageStatistics' => false,

    'hideModeSwitch' => true,

    'toolbarItems' => [
        [
            'heading',
            'bold',
            'italic',
            'strike',
        ],
        [
            // 'hr',
            'quote',
        ],
        [
            'ul',
            'ol',
            // 'task',
            // 'indent',
            // 'outdent',
        ],
        [
            // 'table',
            'image',
            'link',
        ],
        [
            // 'code',
            // 'codeblock',
        ]
    ],

    'plugins' => [
        // 'chart',
        // 'tableMergedCell',
        // 'uml',
        // 'colorSyntax',
        // 'codeSyntaxHighlight'
    ],

    'allowIframe' => false,

    'mediaUploadUrl' => '/api/files',
    'mediaUploadHeaders' => [
        'X-U-Secret-Key' => 'ZnGb9HBW2CZ0'
    ],
];
