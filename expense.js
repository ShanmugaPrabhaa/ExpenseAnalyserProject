document.addEventListener('DOMContentLoaded', function () {
    const addNewBtn = document.getElementById('add-new-btn');
    const expenseForm = document.getElementById('expense-form');
    const expenseIdField = document.getElementById('expense-id');
    const categoryIdField = document.getElementById('category-id');
    const amountField = document.getElementById('amount');
    const descriptionField = document.getElementById('description');
    const dateField = document.getElementById('date');

    addNewBtn.addEventListener('click', function () {
        expenseIdField.value = '';
        categoryIdField.value = '';
        amountField.value = '';
        descriptionField.value = '';
        dateField.value = '';
        expenseForm.style.display = 'block';
    });

    document.querySelectorAll('.edit-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const expenseId = this.dataset.id;
            fetch(`fetch_expense.php?id=${expenseId}`)
                .then(response => response.json())
                .then(data => {
                    expenseIdField.value = data.expense_id;
                    categoryIdField.value = data.category_id;
                    amountField.value = data.amount;
                    descriptionField.value = data.description;
                    dateField.value = data.date;
                    expenseForm.style.display = 'block';
                });
        });
    });

    document.querySelectorAll('.delete-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const expenseId = this.dataset.id;
            if (confirm('Are you sure you want to delete this expense?')) {
                fetch(`delete_expense.php?id=${expenseId}`, { method: 'DELETE' })
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'success') {
                            location.reload();
                        } else {
                            alert('Error deleting expense');
                        }
                    });
            }
        });
    });

    expenseForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(expenseForm);
        fetch('save_expense.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                if (data === 'success') {
                    location.reload();
                } else {
                    alert('Error saving expense');
                }
            });
    });
});
