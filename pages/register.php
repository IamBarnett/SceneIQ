<?php
$pageTitle = "Registro";
require_once '../includes/header.php';

// Si ya está logueado, redirigir
if ($user) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $acceptTerms = isset($_POST['accept_terms']);
    
    // Validar CSRF
    if (!$sceneiq->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } elseif (empty($username) || empty($email) || empty($password) || empty($fullName)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no tiene un formato válido.';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = 'El nombre de usuario debe tener entre 3 y 20 caracteres.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'El nombre de usuario solo puede contener letras, números y guiones bajos.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (!$acceptTerms) {
        $error = 'Debes aceptar los términos y condiciones.';
    } else {
        if ($sceneiq->registerUser($username, $email, $password, $fullName)) {
            $success = 'Registro exitoso. Ya puedes iniciar sesión.';
            // Limpiar formulario
            $_POST = [];
        } else {
            $error = 'Error al registrar. El email o nombre de usuario ya existe.';
        }
    }
}

$genres = $sceneiq->getGenres();
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>¡Únete a SceneIQ!</h1>
            <p>Crea tu cuenta y descubre tu próxima obsesión cinematográfica</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo escape($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo escape($success); ?>
                <a href="login.php">Iniciar sesión ahora</a>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $sceneiq->generateCSRFToken(); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Nombre completo</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo escape($_POST['full_name'] ?? ''); ?>" 
                           required autocomplete="name">
                    <span class="form-icon">👤</span>
                </div>
                
                <div class="form-group">
                    <label for="username">Nombre de usuario</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo escape($_POST['username'] ?? ''); ?>" 
                           required autocomplete="username">
                    <span class="form-icon">@</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo escape($_POST['email'] ?? ''); ?>" 
                       required autocomplete="email">
                <span class="form-icon">📧</span>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                    <span class="form-icon">🔒</span>
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                    <span class="form-icon">🔒</span>
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">👁️</button>
                </div>
            </div>
            
            <!-- Selección de géneros favoritos -->
            <div class="form-group">
                <label>Selecciona tus géneros favoritos (opcional)</label>
                <div class="genre-selection">
                    <?php foreach ($genres as $genre): ?>
                        <label class="genre-checkbox">
                            <input type="checkbox" name="favorite_genres[]" value="<?php echo $genre['id']; ?>">
                            <span class="genre-label" style="--genre-color: <?php echo $genre['color']; ?>">
                                <?php echo escape($genre['name']); ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-group form-checkbox">
                <input type="checkbox" id="accept_terms" name="accept_terms" required>
                <label for="accept_terms">
                    Acepto los <a href="terms.php" target="_blank">términos y condiciones</a> 
                    y la <a href="privacy.php" target="_blank">política de privacidad</a>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">
                Crear Cuenta
            </button>
        </form>
        
        <div class="auth-footer">
            <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = field.nextElementSibling;
    
    if (field.type === 'password') {
        field.type = 'text';
        toggle.textContent = '🙈';
    } else {
        field.type = 'password';
        toggle.textContent = '👁️';
    }
}

// Validación en tiempo real
document.getElementById('username').addEventListener('input', function() {
    const username = this.value;
    if (username.length > 0 && username.length < 3) {
        showFieldError(this, 'Mínimo 3 caracteres');
    } else if (username.length > 20) {
        showFieldError(this, 'Máximo 20 caracteres');
    } else if (username && !username.match(/^[a-zA-Z0-9_]+$/)) {
        showFieldError(this, 'Solo letras, números y guiones bajos');
    } else {
        hideFieldError(this);
        // Verificar disponibilidad
        if (username.length >= 3) {
            checkUsernameAvailability(username);
        }
    }
});

document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && password !== confirmPassword) {
        showFieldError(this, 'Las contraseñas no coinciden');
    } else {
        hideFieldError(this);
    }
});

function checkUsernameAvailability(username) {
    fetch('../api/check-username.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.csrfToken
        },
        body: JSON.stringify({ username: username })
    })
    .then(response => response.json())
    .then(data => {
        const field = document.getElementById('username');
        if (data.available) {
            field.classList.add('success');
            field.classList.remove('error');
            showFieldSuccess(field, 'Disponible ✓');
        } else {
            field.classList.add('error');
            field.classList.remove('success');
            showFieldError(field, 'No disponible');
        }
    });
}

function showFieldSuccess(field, message) {
    let successDiv = field.parentNode.querySelector('.field-success');
    if (!successDiv) {
        successDiv = document.createElement('div');
        successDiv.className = 'field-success';
        field.parentNode.appendChild(successDiv);
    }
    successDiv.textContent = message;
}

function showFieldError(field, message) {
    let errorDiv = field.parentNode.querySelector('.field-error');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        field.parentNode.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

function hideFieldError(field) {
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>