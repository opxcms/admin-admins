<?php

namespace Modules\Admin\Admins;

use Core\Foundation\Module\BaseModule;

class Admins extends BaseModule
{
    /** @var string  Module name */
    protected $name = 'admin_admins';

    /** @var string  Module path */
    protected $path = __DIR__;

    /**
     * Get name of file with admins repository.
     *
     * @return  string
     */
    protected function getRepositoryFileName(): string
    {
        return $this->app->getUsersPath(config('auth.providers.administrators.repository'));
    }

    /**
     * Write admins to file.
     *
     * @param array $admins
     *
     * @return  bool
     */
    protected function storeAdmins(array $admins): bool
    {
        $content = "<?php\r\n";
        $content .= "/**\r\n";
        $content .= " * @see https://bcrypt-generator.com/ for password hash generation\r\n";
        $content .= " */\r\n";
        $content .= "return [\r\n";

        foreach ($admins as $admin) {
            $content .= "\t[\r\n";
            foreach ($admin as $key => $value) {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } else {
                    $value = "'{$value}'";
                }
                $content .= "\t\t'{$key}' => {$value},\r\n";
            }
            $content .= "\t],\r\n";
        }

        $content .= '];';

        $fileName = $this->getRepositoryFileName();

        if (file_put_contents($fileName . '~', $content) === strlen($content)) {

            return rename($fileName . '~', $fileName);
        }

        @unlink($fileName . '~');

        return false;
    }

    /**
     * Make base list query.
     *
     * @return  array
     */
    public function getAdminsList(): array
    {
        $fileName = $this->getRepositoryFileName();

        return require $fileName;
    }

    /**
     * Update or create admin record.
     *
     * @param array $admin
     *
     * @return  array
     */
    public function saveAdmin(array $admin): array
    {
        $id = $admin['id'] ?? null;
        unset($admin['id']);

        $admins = $this->getAdminsList();

        if ($id !== null && isset($admins[$id])) {
            // update
            $admins[$id] = array_merge($admins[$id], $admin);
            $admin['id'] = $id;
        } else {
            // create
            $admins[] = $admin;
            $admin['id'] = count($admins) - 1;
        }

        $this->storeAdmins($admins);

        return $admin;
    }
}
