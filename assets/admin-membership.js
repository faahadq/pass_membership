document.addEventListener('DOMContentLoaded', function () {
	const picker = document.querySelector('.aaca-page-picker');
	if (!picker) {
		return;
	}

	const filterInput = picker.querySelector('.aaca-page-filter');
	const options = Array.from(picker.querySelectorAll('.aaca-page-option'));
	const selectAllBtn = picker.querySelector('.aaca-select-all-pages');
	const clearAllBtn = picker.querySelector('.aaca-clear-all-pages');
	const countEl = picker.querySelector('.aaca-selected-count');

	const updateCount = function () {
		const checked = picker.querySelectorAll('input[type="checkbox"]:checked').length;
		if (countEl) {
			countEl.textContent = checked;
		}
	};

	const applyFilter = function () {
		const term = (filterInput.value || '').trim().toLowerCase();

		options.forEach(function (option) {
			const title = option.getAttribute('data-title') || '';
			option.style.display = !term || title.indexOf(term) !== -1 ? '' : 'none';
		});
	};

	if (filterInput) {
		filterInput.addEventListener('input', applyFilter);
	}

	if (selectAllBtn) {
		selectAllBtn.addEventListener('click', function () {
			options.forEach(function (option) {
				if (option.style.display === 'none') {
					return;
				}
				const checkbox = option.querySelector('input[type="checkbox"]');
				if (checkbox) {
					checkbox.checked = true;
				}
			});
			updateCount();
		});
	}

	if (clearAllBtn) {
		clearAllBtn.addEventListener('click', function () {
			options.forEach(function (option) {
				const checkbox = option.querySelector('input[type="checkbox"]');
				if (checkbox) {
					checkbox.checked = false;
				}
			});
			updateCount();
		});
	}

	picker.addEventListener('change', function (event) {
		if (event.target && event.target.matches('input[type="checkbox"]')) {
			updateCount();
		}
	});

	updateCount();
});