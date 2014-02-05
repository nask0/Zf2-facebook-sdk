<?php

namespace FacebookSdk;

use Zend\ModuleManager\ModuleManager,
	Zend\ModuleManager\Feature\AutoloaderProviderInterface,
	Zend\EventManager\EventInterface as Event;

class Module implements AutoloaderProviderInterface
{

	/**
	 * @var \Zend\Di\Di
	 */
	protected $locator;
	protected $config = array(
		'setAppIdInHeadScript' => true,
		'appId' => 'your_app_id',
		'secret' => 'your_secret'
	);

	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\ClassMapAutoloader' => array(
				__DIR__ . '/autoload_classmap.php',
			),
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				),
			),
		);
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function init(ModuleManager $moduleManager)
	{
		// Remember to keep the init() method as lightweight as possible
		$events = $moduleManager->getEventManager();
		$events->attach('loadModules.post', array($this, 'postModulesLoadsListener'), 8);
	}

	/*
	 * Listeners
	 */
	public function postModulesLoadsListener(Event $event)
	{
		/** @var $cl \Zend\Module\Listener\ConfigMerger */
		$cl = $event->getConfigListener();
		$config = $cl->getMergedConfig(false);
        
		$this->config = array_merge($this->config, 
                                    isset($config[__NAMESPACE__]) ? $config[__NAMESPACE__] : array()
                                );
	}

	public function onBootstrap(Event $e)
	{
		/* @var $app \Zend\Mvc\Application */
		$app = $e->getApplication();
		$app->getServiceManager()->setService('Facebook', 
                                              new \Facebook($this->getFacebookUserConfig())
                                            );
	}

	public function getFacebookUserConfig()
    {
        return array(
            'appId'  => $this->config['appId'],
            'secret' => $this->config['secret']
        );
    }
}
