<?php

return [
    // Bedingungsgenerator
    'OR' => 'ODER',
    'Add a cart condition' => 'Bedingung hinzufügen',
    'Add a cart action' => 'Aktion hinzufügen',
    'Add a message' => 'Nachricht hinzufügen',
    'Date Range' => 'Datumsbereich',
    'From' => 'Von',
    'To' => 'Bis',
    'Order' => 'Bestellung',
    'Item Subtotal' => 'Artikel-Zwischensumme',
    'User' => 'Benutzer',
    'Related To' => 'In Beziehung zu',
    'Quantity' => 'Menge',
    'Any qty' => 'Beliebige Menge',
    'Shipping' => 'Versand',
    'Line Items' => 'Artikelpositionen',
    'All line items' => 'Alle Artikelpositionen',
    'Matching line items' => 'Übereinstimmende Artikelpositionen',
    'Buy X, Get Y' => 'Kaufe X, erhalte Y',

    // Felder der Rabattregel
    'Apply to' => 'Anwenden auf',
    'Apply per' => 'Anwenden pro',
    'Per line item' => 'Pro Artikelposition',
    'Per purchasable' => 'Pro Produkt',
    'Discount Type' => 'Rabatttyp',
    'Discount value' => 'Rabattwert',
    'Flat Amount' => 'Festbetrag',
    'Percentage' => 'Prozentsatz',
    'Discount a flat amount' => 'Einen Festbetrag abziehen',
    'Discount a percentage' => 'Einen Prozentsatz abziehen',
    'Customer buys' => 'Kunde kauft',
    'Customer gets' => 'Kunde erhält',
    'Customer buys quantity' => 'Kaufmenge',
    'Customer gets quantity' => 'Erhaltene Menge',
    'Apply repeatedly' => 'Wiederholt anwenden',

    // Felder der Nachrichtenregel
    'Message' => 'Nachricht',
    'e.g. Spend another {amountRemaining} to get {discountAmount} off' => 'z. B. Geben Sie weitere {amountRemaining} aus, um {discountAmount} Rabatt zu erhalten',
    'Create rules to determine when to show this message' => 'Erstellen Sie Regeln, um festzulegen, wann diese Nachricht angezeigt wird',

    // CP-Index
    'Advanced Discounts' => 'Erweiterte Rabatte',
    'New discount' => 'Neuer Rabatt',
    'Add group' => 'Gruppe hinzufügen',
    'No discounts yet.' => 'Noch keine Rabatte.',
    'Code' => 'Code',
    'Created' => 'Erstellt',
    'Updated' => 'Aktualisiert',
    'Discounts reordered.' => 'Rabatte neu angeordnet.',
    "Couldn't reorder discounts." => 'Rabatte konnten nicht neu angeordnet werden.',

    // Bearbeitungsformular
    'Create discount' => 'Rabatt erstellen',
    'Save discount' => 'Rabatt speichern',
    'Stop processing further discounts' => 'Weitere Rabatte nicht mehr verarbeiten',
    'If this discount matches and is applied, no other discounts will be evaluated.' => 'Wenn dieser Rabatt zutrifft und angewendet wird, werden keine weiteren Rabatte mehr geprüft.',
    'Name' => 'Name',
    'Discount code' => 'Rabattcode',
    'Type' => 'Typ',
    'Advanced' => 'Erweitert',
    'Global Cart Conditions' => 'Globale Warenkorb-Bedingungen',
    'Conditions that gate the entire discount.' => 'Bedingungen, die den gesamten Rabatt steuern.',
    'Rules that decide when this group applies.' => 'Regeln, die festlegen, wann diese Gruppe gilt.',
    'Discount name' => 'Rabattname',
    'Overrides the discount name shown on the order for this group.' => 'Überschreibt den für diese Gruppe auf der Bestellung angezeigten Rabattnamen.',
    'Stop processing further groups' => 'Weitere Gruppen nicht mehr verarbeiten',
    'When this group applies, skip the groups below it.' => 'Wenn diese Gruppe gilt, werden die darunterliegenden Gruppen übersprungen.',
    'Leave blank to apply to every applicable order (like a sale). Entering a code requires the code to be entered during checkout for the discount to apply.' => 'Leer lassen, um den Rabatt auf jede zutreffende Bestellung anzuwenden (wie ein Angebot). Wenn ein Code angegeben wird, muss er an der Kasse eingegeben werden, damit der Rabatt gilt.',
    'Cart Conditions' => 'Warenkorb-Bedingungen',
    'Rules that decide when this discount applies.' => 'Regeln, die festlegen, wann dieser Rabatt gilt.',
    'Cart Actions' => 'Warenkorb-Aktionen',
    'Create cart actions to apply when the customer matches the rules above.' => 'Erstellen Sie Aktionen, die angewendet werden, wenn der Kunde die obigen Regeln erfüllt.',
    'Messages' => 'Nachrichten',
    'Create messages to show to customers when they match certain conditions.' => 'Erstellen Sie Nachrichten die Kunden angezeigt werden, wenn sie bestimmte Bedingungen erfüllen.',

    // Validierungsfehler
    'At least one condition is required.' => 'Es ist mindestens eine Bedingung erforderlich.',
    'At least one cart action rule is required.' => 'Es ist mindestens eine Aktionsregel erforderlich.',
    'At least one action is required.' => 'Es ist mindestens eine Aktion erforderlich.',

    // Flash-Meldungen
    'Discount saved.' => 'Rabatt gespeichert.',
    "Couldn't save discount." => 'Rabatt konnte nicht gespeichert werden.',
    'Discount deleted.' => 'Rabatt gelöscht.',
    'Discount not found.' => 'Rabatt nicht gefunden.',

    // Steuerbemessung
    'Tax Basis' => 'Steuerbemessung',
    'After discounts' => 'Nach Rabatten',
    'Before discounts' => 'Vor Rabatten',
    'Use plugin default' => 'Plugin-Standard verwenden',
    'After discounts means tax is calculated after the discounts have been applied. Before discounts means tax is calculated before discounts have been applied.' => 'Nach Rabatten bedeutet, dass die Steuer nach Anwendung der Rabatte berechnet wird. Vor Rabatten bedeutet, dass die Steuer vor Anwendung der Rabatte berechnet wird.',
    'Overrides the plugin setting. After discounts means tax is calculated after the discounts have been applied. Before discounts means tax is calculated before discounts have been applied.' => 'Überschreibt die Plugin-Einstellung. Nach Rabatten bedeutet, dass die Steuer nach Anwendung der Rabatte berechnet wird. Vor Rabatten bedeutet, dass die Steuer vor Anwendung der Rabatte berechnet wird.',
];
