<?php

namespace SilverStripe\Cow\Commands\Release;

use SilverStripe\Cow\Steps\Release\PlanRelease;
use SilverStripe\Cow\Steps\Release\UpdateTranslations;

/**
 * Description of Create
 *
 * @author dmooyman
 */
class Translate extends Release
{
    protected $name = 'release:translate';

    protected $description = 'Translate this release';

    protected function fire()
    {
        // Get arguments
        $version = $this->getInputVersion();
        $project = $this->getProject();
        $branching = $this->getBranching();

        // Build and confirm release plan
        $buildPlan = new PlanRelease($this, $project, $version, $branching);
        $buildPlan->run($this->input, $this->output);
        $releasePlan = $buildPlan->getReleasePlan();

        // Update all translations
        $translate = new UpdateTranslations($this, $project, $releasePlan);
        $translate->run($this->input, $this->output);
    }
}
