<?php
function sha256($data) {
    return hash('sha256', $data);
}

function build_merkle_tree($leaf_data_array) {
    // Step 1: Hash all the leaves
    $level = array_map('sha256', $leaf_data_array);

    // Step 2: Build the tree bottom-up
    while (count($level) > 1) {
        $next_level = [];

        for ($i = 0; $i < count($level); $i += 2) {
            $left = $level[$i];
            $right = $i + 1 < count($level) ? $level[$i + 1] : $left; // duplicate if odd
            $combined = $left . $right;
            $next_level[] = sha256($combined);
        }

        $level = $next_level; // move up the tree
    }

    // Return the Merkle Root
    return $level[0] ?? null;
}
