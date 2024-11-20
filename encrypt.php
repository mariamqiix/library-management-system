<?php
function encryptPassword($plainPassword) {
    return password_hash($plainPassword, PASSWORD_BCRYPT);
}

function comparePasswords($plainPassword, $hashedPassword) {
    return password_verify($plainPassword, $hashedPassword);
}
?>