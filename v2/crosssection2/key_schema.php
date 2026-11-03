<?php

/** Preserve the source column's identity rules in report keys. */
function crosssection_key_column_definition(array $column): string
{
    $type = $column['Type'] ?? '';
    if (!preg_match('/^(?:var)?(?:char|binary)\([1-9][0-9]*\)$/i', $type)) {
        throw new RuntimeException('Unsupported cross-sectional report key type.');
    }

    $definition = $type;
    $collation = $column['Collation'] ?? null;
    if ($collation !== null) {
        if (!preg_match('/^([a-z0-9]+)_[a-z0-9_]+$/i', $collation, $matches)) {
            throw new RuntimeException('Invalid cross-sectional report key collation.');
        }
        $definition .= ' CHARACTER SET ' . $matches[1] . ' COLLATE ' . $collation;
    }

    return $definition . ' NOT NULL';
}
