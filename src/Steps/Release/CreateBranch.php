<?php

namespace SilverStripe\Cow\Steps\Release;

use SilverStripe\Cow\Commands\Command;
use SilverStripe\Cow\Model\Project;
use SilverStripe\Cow\Steps\Step;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Branch each module to a new temp branch (unless it's already on that branch)
 */
class CreateBranch extends Step {

	/**
	 * @var Project
	 */
	protected $project;

	/**
	 * Branch name
	 *
	 * @var string|null
	 */
	protected $branch;

	/**
	 * Create branch step
	 *
	 * @param Command $command
	 * @param string $directory Where to translate
	 * @param string|null $branch Branch name, if necessary
	 */
	public function __construct(Command $command, $directory, $branch) {
		parent::__construct($command);

		$this->project = new Project($directory);
		$this->branch = $branch;
	}

	/**
	 * @return Project
	 */
	public function getProject() {
		return $this->project;
	}

	/**
	 * @return string|null
	 */
	public function getBranch() {
		return $this->branch;
	}

	public function getStepName() {
		return 'branch';
	}

	public function run(InputInterface $input, OutputInterface $output) {
		$branch = $this->getBranch();
		if(empty($branch)) {
			$this->log($output, "Skipping branch step");
			return;
		}

		$this->log($output, "Branching all modules to <info>{$branch}</info>");
		$modules = $this->getProject()->getModules();
		foreach($modules as $module) {
			$thisBranch = $module->getBranch();
			if($thisBranch != $branch) {
				$this->log(
					$output,
					"Branching module ".$module->getName()." from <info>{$thisBranch}</info> to <info>{$branch}</info>"
				);
				$module->changeBranch($branch);
			}
		}
		$this->log($output, 'Branching complete');
	}
}