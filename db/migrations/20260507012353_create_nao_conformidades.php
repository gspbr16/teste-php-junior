<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateNaoConformidades extends AbstractMigration
{
    public function change(): void
    {
        $this->table('nao_conformidades')
            ->addColumn('id_entrega', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_motivo', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('descricao', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addForeignKey('id_entrega', 'entregas', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('id_motivo', 'motivos_nao_conformidade', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addIndex(['id_entrega'])
            ->create();
    }
}