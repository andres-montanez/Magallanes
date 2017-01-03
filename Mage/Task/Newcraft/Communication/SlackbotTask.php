<?php

namespace Mage\Task\Newcraft\Communication;

use Mage\Task\AbstractTask;

/**
 * Send a message to a Slack channel
 * In the message, the following placeholders can be used
 * - {project}
 * - {environment}
 * - {commit}
 * - {branch}
 * - {tag}
 * - {strategy}
 * - {url}
 * - {commit-url}
 */
class SlackbotTask extends AbstractTask
{
    protected static $defaultMessage = 'Deploying *{project} {tag}* to *{environment}* via _{strategy}_. `{branch}` - `{commit}`';
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        $team = $this->getParameter('team');
        $channel = $this->getParameter('channel', 'general');

        if($team === null){
            throw new \UnexpectedValueException('no slack url provided');
        }

        return 'Send Slack message in #' . $channel . ' at ' . $team . ' [newcraft]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     */
    public function run()
    {
        $team = $this->getParameter('team');
        $channel = $this->getParameter('channel', 'general');
        $token = $this->getParameter('token');

        if(!$team || !$channel || !$token){
            throw new \UnexpectedValueException('not all data required for slack message provided');
        } else {
            $callUrl = 'https://' . $team . '.slack.com/services/hooks/slackbot?token=' .$token. '&channel=%23' . $channel;
        }

        $message = $this->getParameter('message', static::$defaultMessage);

        $replacementArray = [
            '{project}' => $this->getConfig()->general('name'),
            '{url}' => $this->getConfig()->deployment('url'),
            '{environment}' => $this->getConfig()->getEnvironment(),
            '{strategy}' => $this->getConfig()->deployment('strategy'),
            '{commit}' => exec('git rev-parse --short HEAD'),
            '{branch}' => exec('git rev-parse --abbrev-ref HEAD'),
            '{tag}' => str_replace(PHP_EOL,', ',exec('git tag -l --contains HEAD')),
	        '{username}' => exec('git config user.name')
        ];
        if(false !== strpos($message,'{commit-url')){
            $replacementArray['{commit-url}'] = static::getGithubProjectUrl() . '/commit/' . exec('git rev-parse HEAD');
        }

        $message = strtr($this->getParameter('message', static::$defaultMessage),$replacementArray);

        return $this->runCommandLocal('curl --silent --data ' . escapeshellarg($message) . ' \'' . $callUrl . '\'');
    }

    protected static function getGithubProjectUrl(){
        $gitRemoteUrl = exec('git config --get remote.origin.url');
        if(0 === strpos($gitRemoteUrl,'git@github.com')){
            $projectName = substr($gitRemoteUrl,strpos($gitRemoteUrl,':')+1,-4);
        } elseif(0 === strpos($gitRemoteUrl,'https://github.com')){
            $projectName = substr($gitRemoteUrl,strpos($gitRemoteUrl,'/',8)+1,-4);
        } elseif(empty($gitRemoteUrl)) {
            throw new Exception('cannot determine remote url.');
        } elseif(false === strpos($gitRemoteUrl,'github.com' === 0)) {
            throw new Exception('repository not hosted on github, cannot use this strategy.');
        } else {
            throw new Exception('cannot parse remote url.');
        }
        return 'https://github.com/'.$projectName;
    }
}