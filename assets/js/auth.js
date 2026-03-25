// ── SecureAuth JS ──

// Toggle password visibility
function togglePw(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.style.color = isText ? '' : 'var(--blue)';
}

// Password strength meter
const pwInput = document.getElementById('password');
const pwBar   = document.getElementById('pwBar');
const pwHint  = document.getElementById('pwHint');

if (pwInput && pwBar) {
    pwInput.addEventListener('input', function () {
        const val = this.value;
        let score = 0;
        if (val.length >= 8)          score++;
        if (/[A-Z]/.test(val))        score++;
        if (/[a-z]/.test(val))        score++;
        if (/[0-9]/.test(val))        score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { w: '20%', bg: '#ef4444', label: 'Too weak' },
            { w: '40%', bg: '#f97316', label: 'Weak' },
            { w: '60%', bg: '#eab308', label: 'Fair' },
            { w: '80%', bg: '#22c55e', label: 'Good' },
            { w: '100%',bg: '#16a34a', label: 'Strong' },
        ];

        const level = levels[score - 1] || { w: '0%', bg: '', label: '' };
        pwBar.style.width      = level.w;
        pwBar.style.background = level.bg;
        if (pwHint) {
            pwHint.textContent  = level.label;
            pwHint.style.color  = level.bg;
        }
    });
}

// Confirm password match indicator
const confirmInput = document.getElementById('confirm_password');
const matchHint    = document.getElementById('matchHint');

if (confirmInput && matchHint) {
    confirmInput.addEventListener('input', function () {
        if (!pwInput) return;
        if (this.value === '') {
            matchHint.textContent = '';
            return;
        }
        if (this.value === pwInput.value) {
            matchHint.textContent = '✓ Passwords match';
            matchHint.style.color = '#16a34a';
        } else {
            matchHint.textContent = '✗ Passwords do not match';
            matchHint.style.color = '#dc2626';
        }
    });
}

// Submit button loading state
const form       = document.getElementById('loginForm') || document.getElementById('registerForm');
const submitBtn  = document.getElementById('submitBtn');

if (form && submitBtn) {
    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>Please wait…</span>';
    });
}
