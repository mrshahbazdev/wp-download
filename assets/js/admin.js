(function () {
	'use strict';

	const tabs = document.querySelectorAll('.wpd-tab');
	const panels = document.querySelectorAll('.wpd-panel');
	const searchInput = document.getElementById('wpd-search');
	const selectAllBoxes = document.querySelectorAll('.wpd-select-all');
	const bulkBar = document.querySelector('.wpd-bulk-bar');
	const checkedCount = document.getElementById('wpd-checked-count');

	function switchTab(target) {
		tabs.forEach(t => t.classList.remove('active'));
		panels.forEach(p => p.classList.add('hidden'));

		target.classList.add('active');
		const panel = document.getElementById(target.dataset.target);
		if (panel) {
			panel.classList.remove('hidden');
		}
		filterCards();
	}

	tabs.forEach(tab => {
		tab.addEventListener('click', () => switchTab(tab));
	});

	function filterCards() {
		const term = searchInput.value.toLowerCase().trim();
		const activePanel = document.querySelector('.wpd-panel:not(.hidden)');
		if (!activePanel) return;

		const cards = activePanel.querySelectorAll('.wpd-card');
		let visible = 0;
		cards.forEach(card => {
			const text = card.dataset.search.toLowerCase();
			if (term === '' || text.indexOf(term) !== -1) {
				card.classList.remove('hidden');
				visible++;
			} else {
				card.classList.add('hidden');
			}
		});

		const empty = activePanel.querySelector('.wpd-empty');
		if (empty) {
			empty.style.display = visible ? 'none' : 'block';
		}

		selectAllBoxes.forEach(box => { box.checked = false; });
		updateBulkBar();
	}

	if (searchInput) {
		searchInput.addEventListener('input', filterCards);
	}

	function updateBulkBar() {
		const checked = document.querySelectorAll('.wpd-card__checkbox:checked');
		if (checkedCount) {
			checkedCount.textContent = checked.length;
		}
		if (bulkBar) {
			bulkBar.classList.toggle('visible', checked.length > 0);
		}
	}

	document.querySelectorAll('.wpd-card__checkbox').forEach(box => {
		box.addEventListener('change', updateBulkBar);
	});

	selectAllBoxes.forEach(box => {
		box.addEventListener('change', function () {
			const panel = box.closest('.wpd-panel');
			if (!panel) return;
			panel.querySelectorAll('.wpd-card__checkbox').forEach(child => {
				if (!child.closest('.wpd-card').classList.contains('hidden')) {
					child.checked = box.checked;
				}
			});
			updateBulkBar();
		});
	});

	const bulkForm = document.getElementById('wpd-bulk-form');
	if (bulkForm) {
		bulkForm.addEventListener('submit', function () {
			const checked = document.querySelectorAll('.wpd-card__checkbox:checked');
			const itemsInput = document.getElementById('wpd-items');
			if (itemsInput) {
				itemsInput.value = Array.from(checked).map(c => c.value).join(',');
			}
		});
	}
})();
