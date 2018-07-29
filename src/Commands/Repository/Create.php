<?php

namespace SilverStripe\Cow\Commands\Repository;

use Github\Client;
use Github\Exception\ApiLimitExceedException;
use Github\Exception\RuntimeException;
use SilverStripe\Cow\Commands\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;

class Create extends Command
{
    protected $name = 'repository:create';

    protected $description = 'Create a repository on GitHub with the correct settings';

    protected $expectedStartStrings = array(
        'silverstripe-',
        'recipe-',
        'cwp-',
    );

    protected function fire()
    {
        $client = new Client();

//        $client->authenticate('')

        $repositories = [];
        $page = 1;
        do {
            try {
                $repoPart = $client->api('organizations')->repositories($this->getOrganisation(), 'all', $page);
            } catch (ApiLimitExceedException $e) {
                if ($this->input->isInteractive()) {
                    $helper = $this->getHelper('question');
                    $question = new Question('GitHub API Token required to continue, please enter it: ');
                    $question->setHidden(true);
                    $apiToken = $helper->ask($this->input, $this->output, $question);
                    if ($apiToken) {
                        $client->authenticate($apiToken, Client::AUTH_HTTP_TOKEN);
                        $repoPart = $client->api('organizations')->repositories($this->getOrganisation(), 'all', $page);
                    }
                }
            }
            ++$page;
            $repositories = array_merge($repositories, $repoPart);
        } while (!empty($repoPart));

        $commonStarts = [];

        foreach ($repositories as $repo) {
            $nameParts = explode('-', $repo['name'], 2);
            if (!array_key_exists($nameParts[0], $commonStarts)) {
                $commonStarts[$nameParts[0]] = 0;
            }
            ++$commonStarts[$nameParts[0]];
        }

        asort($commonStarts);
        var_export($commonStarts);die;

        // make sure repo doesn't already exist
        try {
            $repo = $client->api('repos')->show($this->getOrganisation(), $this->getRepoName());
        } catch (RuntimeException $e) {

        }

        throw new \LogicException('That repository already exists, cannot create');

        var_dump($repo);
    }

    /**
     * Setup custom options for this command
     */
    protected function configureOptions()
    {
        $this
            ->addArgument('repository', InputArgument::REQUIRED, 'The name of the repository to create on GitHub')
            ->addOption('organisation', 'o', InputArgument::OPTIONAL, 'The organisation to add the repository to', 'silverstripe')
        ;
    }

    protected function getRepoName()
    {
        return $this->input->getArgument('repository');
    }

    protected function getOrganisation()
    {
        return $this->input->getOption('organisation');
    }
}