<?php
namespace Wrep\Bundle\BugsnagBundle\Console;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BugsnagConsoleApplication extends Application
{
	protected $bugsnag;
	
	public function __construct(KernelInterface $kernel)
	{
		parent::__construct($kernel);

		// Boot kernel now
		$kernel->boot();

		// Get container
		$container = $kernel->getContainer();

		// Figure out environment
		$envName = $container->getParameter('kernel.environment');
		$releaseStage = ($envName == 'prod') ? 'production' : $envName;

		$this->bugsnag = new \Bugsnag_Client($container->getParameter('bugsnag.api_key'));
		$this->bugsnag->setReleaseStage($releaseStage);
		$this->bugsnag->setNotifyReleaseStages($container->getParameter('bugsnag.notify_stages'));
		$this->bugsnag->setProjectRoot(realpath($container->getParameter('kernel.root_dir').'/..'));
	}

	public function renderException($e, $output)
	{
		// Send exception to Bugsnag
		$this->bugsnag->notifyException($e);

		// Call parent function
		parent::renderException($e, $output);
	}
}
