<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMotivosNaoConformidade extends AbstractMigration
{
    public function change(): void
    {
        $this->table('motivos_nao_conformidade')
            ->addColumn('codigo', 'string', ['limit' => 30, 'null' => false])
            ->addColumn('descricao', 'string', ['limit' => 150, 'null' => false])
            ->addColumn('ativo', 'boolean', ['default' => true, 'null' => false])
            ->addIndex(['codigo'], ['unique' => true])
            ->create();
    }
}