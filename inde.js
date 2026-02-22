// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    createParticles();
    initInputEffects();
    initFormValidation();
});

// ===== PARTÍCULAS FLOTANTES =====
function createParticles() {
    const particlesContainer = document.getElementById('particles');
    if (!particlesContainer) return;
    
    const particleCount = 40;
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        // Tamaño aleatorio
        const size = Math.random() * 6 + 2;
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        
        // Posición horizontal aleatoria
        particle.style.left = Math.random() * 100 + '%';
        
        // Retraso de animación aleatorio
        particle.style.animationDelay = Math.random() * 15 + 's';
        
        // Duración aleatoria
        particle.style.animationDuration = (Math.random() * 15 + 15) + 's';
        
        particlesContainer.appendChild(particle);
    }
}

// ===== EFECTOS EN INPUTS =====
function initInputEffects() {
    const inputs = document.querySelectorAll('.form-control-premium');
    
    inputs.forEach(input => {
        // Efecto al enfocar
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        // Efecto al desenfocar
        input.addEventListener('blur', function() {
            if (this.value === '') {
                this.parentElement.classList.remove('focused');
            }
        });
        
        // Verificar si ya tiene valor al cargar
        if (input.value !== '') {
            input.parentElement.classList.add('focused');
        }
    });
}

// ===== MOSTRAR/OCULTAR CONTRASEÑA =====
function togglePassword() {
    const passwordInput = document.getElementById('passwordInput');
    const toggleButton = document.getElementById('togglePassword');
    const icon = toggleButton.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye-slash-fill');
        icon.classList.add('bi-eye-fill');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye-fill');
        icon.classList.add('bi-eye-slash-fill');
    }
}

// ===== VALIDACIÓN DEL FORMULARIO =====
function initFormValidation() {
    const form = document.getElementById('loginForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        const email = document.getElementById('emailInput');
        const password = document.getElementById('passwordInput');
        let isValid = true;
        
        // Validar email
        if (!validateEmail(email.value)) {
            showInputError(email, 'Ingresa un correo válido');
            isValid = false;
        } else {
            removeInputError(email);
        }
        
        // Validar contraseña
        if (password.value.length < 4) {
            showInputError(password, 'La contraseña debe tener al menos 4 caracteres');
            isValid = false;
        } else {
            removeInputError(password);
        }
        
        if (!isValid) {
            e.preventDefault();
        } else {
            // Animación de carga
            const btn = document.querySelector('.btn-premium');
            const btnText = btn.querySelector('.btn-text');
            const btnIcon = btn.querySelector('i');
            
            btnText.textContent = 'Iniciando sesión';
            btnIcon.className = 'bi bi-arrow-repeat spin';
            btn.style.pointerEvents = 'none';
        }
    });
}

// ===== VALIDAR EMAIL =====
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// ===== MOSTRAR ERROR EN INPUT =====
function showInputError(input, message) {
    removeInputError(input);
    
    const group = input.parentElement;
    const error = document.createElement('div');
    error.className = 'input-error';
    error.innerHTML = `<i class="bi bi-exclamation-circle"></i> ${message}`;
    error.style.cssText = `
        color: var(--danger);
        font-size: 0.85rem;
        margin-top: 8px;
        margin-left: 15px;
        display: flex;
        align-items: center;
        gap: 5px;
        animation: slideDown 0.3s ease;
    `;
    
    group.appendChild(error);
    input.style.borderColor = 'var(--danger)';
}

// ===== REMOVER ERROR DEL INPUT =====
function removeInputError(input) {
    const group = input.parentElement;
    const error = group.querySelector('.input-error');
    
    if (error) {
        error.remove();
    }
    
    input.style.borderColor = '';
}

// ===== AÑADIR ANIMACIÓN SPIN =====
const style = document.createElement('style');
style.textContent = `
    .spin {
        animation: spin 1s linear infinite;
        display: inline-block;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

// ===== EFECTO DE ESCRITURA EN EL TÍTULO =====
function typeWriterEffect() {
    const title = document.querySelector('.card-title');
    if (!title) return;
    
    const originalText = title.textContent;
    title.textContent = '';
    title.style.borderRight = '3px solid var(--primary)';
    
    let i = 0;
    function type() {
        if (i < originalText.length) {
            title.textContent += originalText.charAt(i);
            i++;
            setTimeout(type, 100);
        } else {
            title.style.borderRight = 'none';
        }
    }
    
    // Comentar si no quieres el efecto de escritura
    // type();
}

// Descomentar si quieres el efecto de escritura
// typeWriterEffect();