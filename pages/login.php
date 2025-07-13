<?php
$pageTitle = "Iniciar Sesión";
require_once '../includes/header.php';

// Si ya está logueado, redirigir
if ($user) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validar CSRF
    if (!$sceneiq->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no tiene un formato válido.';
    } else {
        if ($sceneiq->loginUser($email, $password)) {
            // Si marcó "recordarme", establecer cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + COOKIE_LIFETIME, '/');
                // Aquí guardarías el token en la BD asociado al usuario
            }
            
            $redirectUrl = $_GET['redirect'] ?? '../index.php';
            redirect($redirectUrl);
        } else {
            $error = 'Email o contraseña incorrectos.';
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>¡Bienvenido de vuelta!</h1>
            <p>Inicia sesión para continuar descubriendo contenido increíble</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo escape($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo escape($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $sceneiq->generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo escape($_POST['email'] ?? ''); ?>" 
                       required autocomplete="email">
                <span class="form-icon">📧</span>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
                <span class="form-icon">🔒</span>
                <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
            </div>
            
            <div class="form-group form-checkbox">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Recordarme por 30 días</label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">
                Iniciar Sesión
            </button>
        </form>
        
        <div class="auth-footer">
            <p><a href="forgot-password.php">¿Olvidaste tu contraseña?</a></p>
            <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
        </div>
        
        <!-- Demo credentials -->
        <div class="demo-section">
            <h4>Cuentas de prueba:</h4>
            <div class="demo-accounts">
                <button type="button" class="demo-btn" onclick="fillDemo('admin@sceneiq.com', 'admin123')">
                    👑 Admin Demo
                </button>
                <button type="button" class="demo-btn" onclick="fillDemo('user@sceneiq.com', 'user123')">
                    👤 Usuario Demo
                </button>
            </div>
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

function fillDemo(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}

// Validación en tiempo real
document.getElementById('email').addEventListener('blur', function() {
    const email = this.value;
    if (email && !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        this.classList.add('error');
        showFieldError(this, 'Email no válido');
    } else {
        this.classList.remove('error');
        hideFieldError(this);
    }
});

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