<?php

return [

    'label' => 'دروستکەری پرسیار',

    'form' => [

        'operator' => [
            'label' => 'ئۆپەراتۆر',
        ],

        'or_groups' => [

            'label' => 'گروپەکان',

            'block' => [
                'label' => 'جیابوونەوە (یان)',
                'or' => 'یان',
            ],

        ],

        'rules' => [

            'label' => 'ڕێساکان',

            'item' => [
                'and' => 'و',
            ],

        ],

    ],

    'no_rules' => '(بێ یاسا)',

    'item_separators' => [
        'and' => 'و',
        'or' => 'یان',
    ],

    'operators' => [

        'is_filled' => [

            'label' => [
                'direct' => 'پڕ دەکرێتەوە',
                'inverse' => 'بەتاڵە',
            ],

            'summary' => [
                'direct' => ':attribute پڕ دەکرێتەوە',
                'inverse' => ':attribute بەتاڵە',
            ],

        ],

        'boolean' => [

            'is_true' => [

                'label' => [
                    'direct' => 'ڕاستە',
                    'inverse' => 'هەڵەیە',
                ],

                'summary' => [
                    'direct' => ':attribute ڕاستە',
                    'inverse' => ':attribute هەڵەیە',
                ],

            ],

        ],

        'date' => [

            'is_after' => [

                'label' => [
                    'direct' => 'دوای ئەوە یە',
                    'inverse' => 'دوای ئەوە نیە',
                ],

                'summary' => [
                    'direct' => ':attribute دوای :date یە',
                    'inverse' => ':attribute دوای :date نیە',
                ],

            ],

            'is_before' => [

                'label' => [
                    'direct' => 'پێشترە',
                    'inverse' => 'پێشتر نییە',
                ],

                'summary' => [
                    'direct' => ':attribute پێشترە له :date',
                    'inverse' => ':attribute پێشترە نیه له :date',
                ],

            ],

            'is_date' => [

                'label' => [
                    'direct' => 'بەروارە',
                    'inverse' => 'بەروار نییە',
                ],

                'summary' => [
                    'direct' => ':attribute بریتیە لە :date',
                    'inverse' => ':attribute :date نییە',
                ],

            ],

            'is_month' => [

                'label' => [
                    'direct' => 'مانگە',
                    'inverse' => 'مانگێک نییە',
                ],

                'summary' => [
                    'direct' => ':attribute بریتیە لە :month',
                    'inverse' => ':attribute :month نییە',
                ],

            ],

            'is_year' => [

                'label' => [
                    'direct' => 'ساڵ',
                    'inverse' => 'ساڵێک نییە',
                ],

                'summary' => [
                    'direct' => ':attribute بریتیە لە :year',
                    'inverse' => ':attribute :year نییە',
                ],

            ],

            'form' => [

                'date' => [
                    'label' => 'ڕێکەوت',
                ],

                'month' => [
                    'label' => 'مانگ',
                ],

                'year' => [
                    'label' => 'ساڵ',
                ],

            ],

        ],

        'number' => [

            'equals' => [

                'label' => [
                    'direct' => 'یەکسانە',
                    'inverse' => 'یەکسان نییە',
                ],

                'summary' => [
                    'direct' => ':attribute یەکسانە بە :number',
                    'inverse' => ':attribute یەکسان نییە بە :number',
                ],

            ],

            'is_max' => [

                'label' => [
                    'direct' => 'زۆرترین',
                    'inverse' => 'گەورەتر',
                ],

                'summary' => [
                    'direct' => ':attribute زۆرترینە بە :number',
                    'inverse' => ':attribute گەورەتر له :number',
                ],

            ],

            'is_min' => [

                'label' => [
                    'direct' => 'کەمترین',
                    'inverse' => 'کەمتر لە',
                ],

                'summary' => [
                    'direct' => ':attribute کەمترینە :number',
                    'inverse' => ':attribute کەمتر لە :number',
                ],

            ],

            'aggregates' => [

                'average' => [
                    'label' => 'ڕێژە',
                    'summary' => 'ناوەندی :attribute',
                ],

                'max' => [
                    'label' => 'الأعلى',
                    'summary' => 'الأعلى :attribute',
                ],

                'min' => [
                    'label' => 'الأدنى',
                    'summary' => 'الأدنى :attribute',
                ],

                'sum' => [
                    'label' => 'المجموع',
                    'summary' => 'مجموع :attribute',
                ],

            ],

            'form' => [

                'aggregate' => [
                    'label' => 'کۆی گشتی',
                ],

                'number' => [
                    'label' => 'ژمارەکە',
                ],

            ],

        ],

        'relationship' => [

            'equals' => [

                'label' => [
                    'direct' => 'يملك',
                    'inverse' => 'لا يملك',
                ],

                'summary' => [
                    'direct' => 'يملك :count :relationship',
                    'inverse' => 'لا يملك :count :relationship',
                ],

            ],

            'has_max' => [

                'label' => [
                    'direct' => 'يملك الحد الأقصى',
                    'inverse' => 'يملك أكثر من',
                ],

                'summary' => [
                    'direct' => 'يملك كحد أقصى :count :relationship',
                    'inverse' => 'يملك أكثر من :count :relationship',
                ],

            ],

            'has_min' => [

                'label' => [
                    'direct' => 'يملك الحد الأدنى',
                    'inverse' => 'يملك أقل من',
                ],

                'summary' => [
                    'direct' => 'يملك كحد أدنى :count :relationship',
                    'inverse' => 'يملك أقل من :count :relationship',
                ],

            ],

            'is_empty' => [

                'label' => [
                    'direct' => 'فارغ',
                    'inverse' => 'ليس فارغا',
                ],

                'summary' => [
                    'direct' => ':relationship فارغ',
                    'inverse' => ':relationship ليس فارغاً',
                ],

            ],

            'is_related_to' => [

                'label' => [

                    'single' => [
                        'direct' => 'بریتیە لە',
                        'inverse' => 'نییە',
                    ],

                    'multiple' => [
                        'direct' => 'لەخۆ دەگرێت',
                        'inverse' => 'لەخۆناگرێت',
                    ],

                ],

                'summary' => [

                    'single' => [
                        'direct' => ':relationship بریتیە لە :values',
                        'inverse' => ':relationship بریتی نیە لە :values',
                    ],

                    'multiple' => [
                        'direct' => ':relationship لەخۆ دەگرێت :values',
                        'inverse' => ':relationship لەخۆناگرێت :values',
                    ],

                    'values_glue' => [
                        0 => '، ',
                        'final' => ' یان ',
                    ],

                ],

                'form' => [

                    'value' => [
                        'label' => 'بەها',
                    ],

                    'values' => [
                        'label' => 'بەهاکان',
                    ],

                ],

            ],

            'form' => [

                'count' => [
                    'label' => 'گێرانەوە',
                ],

            ],

        ],

        'select' => [

            'is' => [

                'label' => [
                    'direct' => 'بریتیە لە',
                    'inverse' => 'نییە',
                ],

                'summary' => [
                    'direct' => ':attribute هو :values',
                    'inverse' => ':attribute ليس :values',
                    'values_glue' => [
                        ', ' => '، ',
                        'final' => ' أو ',
                    ],
                ],

                'form' => [

                    'value' => [
                        'label' => 'القيمة',
                    ],

                    'values' => [
                        'label' => 'القيم',
                    ],

                ],

            ],

        ],

        'text' => [

            'contains' => [

                'label' => [
                    'direct' => 'يحتوي',
                    'inverse' => 'لا يحتوي',
                ],

                'summary' => [
                    'direct' => ':attribute يحتوي :text',
                    'inverse' => ':attribute لا يحتوي :text',
                ],

            ],

            'ends_with' => [

                'label' => [
                    'direct' => 'ينتهي بـ',
                    'inverse' => 'لا ينتهي بـ',
                ],

                'summary' => [
                    'direct' => ':attribute ينتهي بـ :text',
                    'inverse' => ':attribute لا ينتهي بـ :text',
                ],

            ],

            'equals' => [

                'label' => [
                    'direct' => 'يساوي',
                    'inverse' => 'لا يساوي',
                ],

                'summary' => [
                    'direct' => ':attribute يساوي :text',
                    'inverse' => ':attribute لا يساوي :text',
                ],

            ],

            'starts_with' => [

                'label' => [
                    'direct' => 'يبدأ بـ',
                    'inverse' => 'لا يبدأ بـ',
                ],

                'summary' => [
                    'direct' => ':attribute يبدأ بـ :text',
                    'inverse' => ':attribute لا يبدأ بـ :text',
                ],

            ],

            'form' => [

                'text' => [
                    'label' => 'النص',
                ],

            ],

        ],

    ],

    'actions' => [

        'add_rule' => [
            'label' => 'یاسایەک زیاد بکە',
        ],

        'add_rule_group' => [
            'label' => 'کۆمەڵە یاسایەک زیاد بکە',
        ],

    ],

];
