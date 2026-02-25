<style>
@keyframes modalIn {
    from {
        opacity: 0;
        transform: translateY(12px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-enter {
    animation: modalIn 220ms ease-out;
}

body.nomina-modal-open {
    overflow: hidden;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.body.classList.add('nomina-modal-open');
});

window.addEventListener('beforeunload', () => {
    document.body.classList.remove('nomina-modal-open');
});
</script>
