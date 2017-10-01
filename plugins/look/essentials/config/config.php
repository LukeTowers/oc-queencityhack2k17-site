<?php

return [
    'long_date_format'          => 'F jS, Y',
    'long_datetime_format'      => 'F jS, Y \a\t g:ia',
    'condensed_date_format'     => 'Y-m-d',
    'condensed_datetime_format' => 'Y-m-d H:i',
    
    'packages' => [
        'luketowers/purifier' => [
            'providers' => [
                '\LukeTowers\Purifier\PurifierServiceProvider',
            ],
            'aliases' => [
                'Purifier' => '\LukeTowers\Purifier\Facades\Purifier',
            ],
            'config_namespace' => 'purifier',
            'config' => [
                'encoding'      => 'UTF-8',
                'finalize'      => true,
                'cachePath'     => storage_path('app/purifier'),
                'cacheFileMode' => 0755,
                'settings'      => [
                    'default' => [
                        'HTML.Doctype'             => 'HTML 4.01 Transitional',
                        'HTML.Allowed'             => 'div[style],b,strong,i,em,u,a[href|title|class|style|target],ul,ol,li,p[style|class],br,span[class|style],img[width|height|alt|src|class|style],blockquote,table[class|style|summary],thead,tbody,tr,td[abbr|style|colspan|rowspan],th[abbr|style|colspan|rowspan],h1,h2,h3,h4,pre',
                        'CSS.AllowedProperties'    => 'background,background-color,color,vertical-align,text-align,width,height,margin,padding',
                        'AutoFormat.AutoParagraph' => true,
			            'AutoFormat.RemoveEmpty'   => false,
                    ],
                    'test'    => [
                        'Attr.EnableID' => 'true',
                    ],
                    "youtube" => [
                        "HTML.SafeIframe"      => 'true',
                        "URI.SafeIframeRegexp" => "%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/)%",
                    ],
                    'custom_definition' => [
			            'id'  => 'html5-definitions',
			            'rev' => 1,
			            'debug' => false,
			            'elements' => [
			                // http://developers.whatwg.org/sections.html
			                ['section', 'Block', 'Flow', 'Common'],
			                ['nav',     'Block', 'Flow', 'Common'],
			                ['article', 'Block', 'Flow', 'Common'],
			                ['aside',   'Block', 'Flow', 'Common'],
			                ['header',  'Block', 'Flow', 'Common'],
			                ['footer',  'Block', 'Flow', 'Common'],
							
							// Content model actually excludes several tags, not modelled here
			                ['address', 'Block', 'Flow', 'Common'],
			                ['hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common'],
							
							// http://developers.whatwg.org/grouping-content.html
			                ['figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common'],
			                ['figcaption', 'Inline', 'Flow', 'Common'],
							
							// http://developers.whatwg.org/the-video-element.html#the-video-element
			                ['video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
			                    'src' => 'URI',
								'type' => 'Text',
								'width' => 'Length',
								'height' => 'Length',
								'poster' => 'URI',
								'preload' => 'Enum#auto,metadata,none',
								'controls' => 'Bool',
			                ]],
			                ['source', 'Block', 'Flow', 'Common', [
								'src' => 'URI',
								'type' => 'Text',
			                ]],
			
							// http://developers.whatwg.org/text-level-semantics.html
			                ['s',    'Inline', 'Inline', 'Common'],
			                ['var',  'Inline', 'Inline', 'Common'],
			                ['sub',  'Inline', 'Inline', 'Common'],
			                ['sup',  'Inline', 'Inline', 'Common'],
			                ['mark', 'Inline', 'Inline', 'Common'],
			                ['wbr',  'Inline', 'Empty', 'Core'],
							
							// http://developers.whatwg.org/edits.html
			                ['ins', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']],
			                ['del', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']],
			            ],
			            'attributes' => [
			                ['iframe', 'allowfullscreen', 'Bool', true],
			                ['table', 'height', 'Text'],
			                ['td', 'border', 'Text'],
			                ['th', 'border', 'Text'],
			                ['tr', 'width', 'Text'],
			                ['tr', 'height', 'Text'],
			                ['tr', 'border', 'Text'],
			            ],
			        ],
			        'custom_attributes' => [
			            ['a', 'target', 'Enum#_blank,_self,_target,_top'],
			        ],
			        'custom_elements' => [
			            ['u', 'Inline', 'Inline', 'Common'],
			        ],
                ],
            ],
        ],
    ],
];
