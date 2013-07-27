<?php
return array(
	'debugconfig' => array(
		'enable' => true,//true=>write cache file
		'cachepath' => __DIR__.'/../../../../data/cache/debug.Ycheukf.html',
	),
    'router' => array(
        'routes' => array(
            'debug' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/debug',
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

