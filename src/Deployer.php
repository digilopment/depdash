<?php

class Deployer
{

    private $folders;
    private $customTagOrBranch;
    private $config;

    public function __construct(array $repositories)
    {
        $this->config = (new Config())->init();
        $folders = [];
        foreach ($repositories as $folder) {
            $folders[] = $this->config['getRepositoriesPath'] . $folder;
        }
        $this->folders = $folders;
    }

    public function deploy(string $customTagOrBranch = null)
    {
        $this->customTagOrBranch = $customTagOrBranch;

        foreach ($this->folders as $folder) {

            chdir($folder);
            shell_exec('git fetch --all');
            $latest_tag_output = shell_exec('git describe --tags $(git rev-list --tags --max-count=1)');
            $latest_tag = trim($latest_tag_output);

            if (!empty($this->customTagOrBranch)) {
                $tag_or_branch = $this->customTagOrBranch;
            } elseif (!empty($latest_tag)) {
                $tag_or_branch = $latest_tag;
            } else {
                $tag_or_branch = 'master';
            }

            shell_exec("git checkout $tag_or_branch");

            echo "Deployed folder: $folder\n";
            echo "Git fetch output:\n";
            echo shell_exec('git fetch --all') . "\n";
            echo "Tag/branch deployed: $tag_or_branch\n";
            echo "Git checkout output:\n";
            echo shell_exec("git checkout $tag_or_branch") . "\n";
        }
    }

    public function getFolders(): array
    {
        return $this->folders;
    }

    public function setFolders(array $folders): void
    {
        $this->folders = $folders;
    }

    public function getCustomTagOrBranch(): ?string
    {
        return $this->customTagOrBranch;
    }

    public function setCustomTagOrBranch(?string $customTagOrBranch): void
    {
        $this->customTagOrBranch = $customTagOrBranch;
    }

}
