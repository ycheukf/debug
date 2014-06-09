<?php
return array(
	'debugconfig' => array(
		'enable' => true,//true=>write cache file
		'allowips' => array(),//allow ips. empty means no forbidden
		'adapter' => 'file',//file|memcache
		'cachepath' => __DIR__.'/../../../../data/cache/ycheukf.debug.html',
		'ignore_request' => array('.css', '.js', '.ico', '.png', '.gif', '.jpg', '.peng', 'oauth/authorize'),
		"memcache_config" => array(
			'servers' => array(
//				array('localhost', 11211, false),
			),
			'jquery_keyname' => 'ycfdebug_jquery_key',
		),
	),
    'router' => array(
        'routes' => array(
            'debug' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/ycfdebug',
                    'defaults' => array(
                         '__NAMESPACE__' => 'YcheukfDebug\Controller',
                        'controller'    => 'IndexController',
                        'action'     => 'index',
                    ),
                ),
            ),
		),
	),
    'controllers' => array(
        'invokables' => array(
            'YcheukfDebug\Controller\IndexController' => 'YcheukfDebug\Controller\IndexController'
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'YcheukfDebug' => __DIR__ . '/../view',
        ),
		'template_map' => array(
			'debug/layout'             => __DIR__ . '/../view/layout/layout.phtml',
		),
    ),
    'service_manager' => array(
		'aliases' => array(
			'Ycheukf_debug_db_adapter' => 'Zend\Db\Adapter\Adapter',
		),
        'factories' => array(
        ),
	),
);

