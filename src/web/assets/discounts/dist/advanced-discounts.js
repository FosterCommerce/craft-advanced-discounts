/* global Craft, Garnish, htmx, $ */

(function ($) {
	'use strict';

	Craft.AdvancedDiscounts = {
		groupDragSort: null,

		/**
		 * Re-runnable: the discount type switcher replaces the whole #panels container.
		 */
		initGroups: function () {
			const container = document.getElementById('panels');
			if (container === null) {
				return;
			}

			if (this.groupDragSort !== null) {
				this.groupDragSort.destroy();
			}

			this.groupDragSort = new Garnish.DragSort(container.querySelectorAll('[data-panel]'), {
				handle: '.move',
				axis: Garnish.Y_AXIS,
			});

			this.moveExcludedIntoBundle(container);
		},

		/**
		 * Buy X, Get Y renders the excluded-variant callout as a sibling of the rule, and it
		 * reads as belonging to the rule.
		 */
		moveExcludedIntoBundle: function (scope) {
			scope.querySelectorAll('.advanced-discount-bundle').forEach(function (bundle) {
				const inner = bundle.querySelector('.advanced-discount-bundle-inner');
				const trigger = bundle.querySelector(':scope > .advanced-discount-excluded-trigger');
				const list = bundle.querySelector(':scope > .advanced-discount-excluded-list');

				if (inner === null || trigger === null) {
					return;
				}

				inner.appendChild(trigger);
				if (list !== null) {
					inner.appendChild(list);
				}
			});
		},

		panelFor: function (key) {
			return document.querySelector('[data-panel][data-panel-key="' + key + '"]');
		},

		/**
		 * Garnish moves a disclosure menu out of the group it belongs to, so its items are
		 * reached by the group's key rather than by walking up from the item.
		 */
		syncPanelMenu: function (panel) {
			const key = panel.dataset.panelKey;
			const collapsed = panel.classList.contains('is-collapsed');
			const enabled = panel.querySelector('[data-panel-enabled]').value === '1';
			const hiddenItems = {
				collapse: collapsed,
				expand: !collapsed,
				disable: !enabled,
				enable: enabled,
			};

			Object.keys(hiddenItems).forEach(function (action) {
				const item = document.querySelector('[data-panel-key="' + key + '"][data-panel-action="' + action + '"]');
				item.closest('li').classList.toggle('hidden', hiddenItems[action]);
			});
		},

		setPanelCollapsed: function (panel, collapsed) {
			panel.classList.toggle('is-collapsed', collapsed);
			this.syncPanelMenu(panel);
		},

		setPanelEnabled: function (panel, enabled) {
			panel.querySelector('[data-panel-enabled]').value = enabled ? '1' : '';
			panel.classList.toggle('is-disabled', !enabled);

			if (enabled) {
				this.syncPanelMenu(panel);
				return;
			}

			this.setPanelCollapsed(panel, true);
		},

		movePanel: function (panel, delta) {
			const sibling = delta < 0 ? panel.previousElementSibling : panel.nextElementSibling;
			if (sibling === null) {
				return;
			}

			if (delta < 0) {
				sibling.before(panel);
			} else {
				sibling.after(panel);
			}

			panel.querySelector('[data-disclosure-trigger]').focus();
		},

		insertToken: function (textarea, token) {
			const start = textarea.selectionStart ?? textarea.value.length;
			const end = textarea.selectionEnd ?? textarea.value.length;

			textarea.value = textarea.value.slice(0, start) + token + textarea.value.slice(end);

			const caret = start + token.length;
			textarea.setSelectionRange(caret, caret);
			textarea.focus();
			$(textarea).trigger('input').trigger('change');
		},
	};

	$(function () {
		Craft.AdvancedDiscounts.initGroups();

		// Message token chips. Delegated, because rules render and re-render on their own.
		$(document).on('click keydown', '.advanced-discount-token-chip', function (event) {
			if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') {
				return;
			}

			event.preventDefault();

			const textarea = $(this).closest('.advanced-discount-message').find('textarea').get(0);
			if (textarea !== undefined) {
				Craft.AdvancedDiscounts.insertToken(textarea, $(this).data('token'));
			}
		});

		$(document).on('dragstart', '.advanced-discount-token-chip', function (event) {
			event.originalEvent.dataTransfer.setData('text/plain', $(this).data('token'));
			event.originalEvent.dataTransfer.effectAllowed = 'copy';
		});

		$(document).on('dragover', '.advanced-discount-message textarea', function (event) {
			event.preventDefault();
		});

		$(document).on('drop', '.advanced-discount-message textarea', function (event) {
			event.preventDefault();

			const token = event.originalEvent.dataTransfer.getData('text/plain');
			if (token) {
				Craft.AdvancedDiscounts.insertToken(this, token);
			}
		});

		// Group controls
		$(document).on('dblclick', '.advanced-discount-panel-header', function (event) {
			if (event.target.closest('.advanced-discount-panel-actions') !== null) {
				return;
			}

			const panel = this.closest('[data-panel]');

			Craft.AdvancedDiscounts.setPanelCollapsed(panel, !panel.classList.contains('is-collapsed'));
		});

		$(document).on('click', '[data-panel-action]', function () {
			const discounts = Craft.AdvancedDiscounts;
			const panel = discounts.panelFor(this.dataset.panelKey);

			switch (this.dataset.panelAction) {
				case 'collapse':
					discounts.setPanelCollapsed(panel, true);
					break;
				case 'expand':
					discounts.setPanelCollapsed(panel, false);
					break;
				case 'disable':
					discounts.setPanelEnabled(panel, false);
					break;
				case 'enable':
					discounts.setPanelEnabled(panel, true);
					break;
				case 'moveUp':
					discounts.movePanel(panel, -1);
					break;
				case 'moveDown':
					discounts.movePanel(panel, 1);
					break;
				case 'delete':
					panel.remove();
					break;
			}
		});

		$(document).on('click', '.advanced-discount-excluded-trigger', function () {
			new Craft.Slideout(this.nextElementSibling.innerHTML);
		});

		// Delegated: the discount type switcher replaces this button along with #panels.
		$(document).on('click', '#add-panel', async function () {
			const container = document.getElementById('panels');
			const { data } = await Craft.sendActionRequest('POST', 'advanced-discounts/manage/panel', {
				data: {
					type: container.dataset.discountType,
				},
			});

			const wrapper = document.createElement('div');
			wrapper.innerHTML = data.html;

			const group = wrapper.firstElementChild;
			container.appendChild(group);

			Craft.appendHeadHtml(data.headHtml);
			Craft.appendBodyHtml(data.bodyHtml);
			Craft.initUiElements(group);
			htmx.process(group);

			Craft.AdvancedDiscounts.groupDragSort.addItems(group);
			Craft.AdvancedDiscounts.moveExcludedIntoBundle(container);
		});

		// Coupons
		$(document).on('click keyup', '#requireCouponCode', function () {
			const on = $(this).hasClass('on');

			$('#coupons-container').toggleClass('hidden', !on);
			if (on) {
				$('#coupons-table').data('editable-table').initialize();
			}
		});

		const generateButton = document.getElementById('generate-coupons');
		if (generateButton !== null) {
			let hud;

			generateButton.addEventListener('click', function () {
				if (hud) {
					hud.show();
					return;
				}

				const couponsTable = $('#coupons-table');
				const editableTable = couponsTable.data('editable-table');
				const body = $('<div/>');

				const countField = Craft.ui.createTextField({
					label: Craft.t('advanced-discounts', 'edit.coupons.generateCount'),
					type: 'number',
					value: 1,
				}).appendTo(body);

				const formatField = Craft.ui.createTextField({
					label: Craft.t('advanced-discounts', 'edit.coupons.generateFormat'),
					instructions: Craft.t('advanced-discounts', 'edit.coupons.generateFormatInstructions'),
					value: 'DISCOUNT_####',
				}).appendTo(body);

				const submitButton = $('<button/>', {
					type: 'button',
					class: 'btn submit',
					text: Craft.t('advanced-discounts', 'edit.coupons.generate'),
				}).appendTo($('<div class="buttons"/>').appendTo(body));

				hud = new Garnish.HUD(generateButton, body);

				submitButton.on('click', async function () {
					const count = parseInt(countField.find('input').val(), 10) || 0;
					if (count < 1) {
						return;
					}

					submitButton.addClass('loading');

					try {
						const existingCodes = couponsTable
							.find('[name$="[code]"]')
							.map(function (index, input) {
								return input.value;
							})
							.get()
							.filter(Boolean);

						const { data } = await Craft.sendActionRequest('POST', 'advanced-discounts/manage/generate-coupons', {
							data: {
								count: count,
								format: formatField.find('input').val(),
								existingCodes: existingCodes,
							},
						});

						(data.coupons || []).forEach(function (code) {
							editableTable.addRow();
							couponsTable.find('tbody tr').last().find('[name$="[code]"]').val(code);
						});

						hud.hide();
					} catch (error) {
						Craft.cp.displayError(error?.response?.data?.message);
					} finally {
						submitButton.removeClass('loading');
					}
				});
			});
		}

		// Discount type switcher
		const typeSelect = document.getElementById('type');
		if (typeSelect !== null) {
			const renderedTypes = {};
			let renderedType = typeSelect.value;

			typeSelect.addEventListener('change', async function () {
				const container = document.getElementById('type-settings');
				const placeholders = document.getElementById('message-placeholders-table');

				// Detached nodes keep their condition builders, so switching back to a type shows
				// the groups the discount was saved with rather than an empty one.
				renderedTypes[renderedType] = {
					nodes: Array.from(container.childNodes),
					placeholdersHtml: placeholders.innerHTML,
				};
				renderedType = typeSelect.value;

				const rendered = renderedTypes[renderedType];
				if (rendered !== undefined) {
					container.replaceChildren(...rendered.nodes);
					placeholders.innerHTML = rendered.placeholdersHtml;
					Craft.AdvancedDiscounts.initGroups();
					return;
				}

				const { data } = await Craft.sendActionRequest('POST', 'advanced-discounts/manage/type-settings', {
					data: {
						type: renderedType,
					},
				});

				container.innerHTML = data.html;
				placeholders.innerHTML = data.placeholdersHtml;

				Craft.appendHeadHtml(data.headHtml);
				Craft.appendBodyHtml(data.bodyHtml);
				Craft.initUiElements(container);
				htmx.process(container);

				Craft.AdvancedDiscounts.initGroups();
			});
		}
	});
})(jQuery);
