<?php

namespace App\Controllers;

use App\Database;

class NaoConformidadeController
{
    public static function indexMotivos(array $params): void
    {
        $db   = Database::connection();
        $stmt = $db->query('SELECT id, codigo, descricao FROM motivos_nao_conformidade WHERE ativo = 1 ORDER BY id ASC');
        $rows = $stmt->fetchAll();

        json(array_map(fn($r) => [
            'id'       => (int) $r['id'],
            'codigo'   => $r['codigo'],
            'descricao' => $r['descricao'],
        ], $rows));
    }

    public static function store(array $params): void
    {
        $data = body();
        $db   = Database::connection();

        // Verifica se a entrega existe
        $stmt = $db->prepare('SELECT id FROM entregas WHERE id = ?');
        $stmt->execute([$params['id']]);
        if (!$stmt->fetch()) {
            json(['erro' => 'Entrega não encontrada'], 404);
        }

        // Valida campo obrigatório
        if (empty($data['id_motivo'])) {
            json(['erro' => 'Campo obrigatório: id_motivo'], 422);
        }

        // Verifica se o motivo existe e está ativo
        $stmt = $db->prepare('SELECT id FROM motivos_nao_conformidade WHERE id = ? AND ativo = 1');
        $stmt->execute([$data['id_motivo']]);
        if (!$stmt->fetch()) {
            json(['erro' => 'Motivo não encontrado ou inativo'], 422);
        }

        // Registra a não conformidade
        $stmt = $db->prepare('
            INSERT INTO nao_conformidades (id_entrega, id_motivo, descricao)
            VALUES (?, ?, ?)
        ');
        $stmt->execute([
            (int) $params['id'],
            (int) $data['id_motivo'],
            $data['descricao'] ?? null,
        ]);

        $id   = $db->lastInsertId();
        $stmt = $db->prepare('
            SELECT nc.id, nc.id_entrega, nc.id_motivo, m.codigo, m.descricao AS motivo_descricao,
                   nc.descricao, nc.created_at
            FROM nao_conformidades nc
            JOIN motivos_nao_conformidade m ON m.id = nc.id_motivo
            WHERE nc.id = ?
        ');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        json([
            'id'         => (int) $row['id'],
            'id_entrega' => (int) $row['id_entrega'],
            'motivo'     => [
                'id'       => (int) $row['id_motivo'],
                'codigo'   => $row['codigo'],
                'descricao' => $row['motivo_descricao'],
            ],
            'descricao'  => $row['descricao'],
            'created_at' => $row['created_at'],
        ], 201);
    }
}