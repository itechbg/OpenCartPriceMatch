# OpenCart Price Match Module

A fully functional **Price Match** extension for **OpenCart 3.x** that allows customers to submit price match requests when they find a product cheaper at a competitor store. Admins can manage all requests from the back office.

---

## Features

- **Customer-facing price match form** loaded via AJAX modal on product pages
- **Pre-fills** customer details (name, email, telephone) when the customer is logged in
- **Captures**: competitor store name, competitor URL, competitor price, and optional comments
- **Email notifications** to admin when a new price match request is submitted
- **Admin panel** to list, view, approve, reject, and delete requests
- **Filterable & sortable** request list with pagination
- **Status management**: Pending / Approved / Rejected with colour-coded badges
- **Admin notes** field to attach internal comments to each request
- **OCMOD-based** product page integration — no core file edits required

---

## Requirements

- OpenCart **3.x** (tested on 3.0.x)
- PHP 5.6+
- MySQL 5.5+

---

## Installation

### Via OpenCart Extension Installer (recommended)

1. Zip the contents of the `upload/` folder (keeping the directory structure intact).
2. In the OpenCart admin, go to **Extensions → Installer**.
3. Upload the zip file.
4. Go to **Extensions → Modifications** and click **Refresh**.
5. Go to **Extensions → Extensions**, select **Modules** from the dropdown.
6. Find **Price Match** and click **Install** (the green `+` button), then click **Edit** (pencil icon).

### Manual Installation

1. Copy everything inside the `upload/` directory to your OpenCart root (merge with existing directories).
2. In the admin, go to **Extensions → Modifications** and click **Refresh**.
3. Go to **Extensions → Extensions → Modules**, find **Price Match**, click **Install**, then **Edit**.

---

## Configuration

Navigate to **Extensions → Extensions → Modules → Price Match → Edit**:

| Option | Description |
|--------|-------------|
| **Status** | Enable or disable the module globally |
| **Email Notification** | Send an email to the admin when a new request is submitted |
| **Notification Email** | Admin email address to receive notifications (falls back to store email) |
| **Default Request Status** | Status assigned to new requests: *Pending* (0) or *Approved* (1) |

---

## Managing Requests

After enabling the module, go to **Extensions → Extensions → Modules → Price Match → Edit** and click **Request List** (or navigate directly via the breadcrumb/button).

### Request List

- **Filter** by product name, customer email, or status
- **Sort** by any column
- **Delete** one or more selected requests
- **View** a request to see full details

### Viewing a Request

Each request shows:

- Product name (links to the product edit page)
- Customer name, email, and telephone
- Competitor store name, URL, and price
- Customer's additional comments
- Date submitted

You can:

- **Change the status** (Pending / Approved / Rejected)
- **Add admin notes/comments** visible only to admins

---

## How It Works (Frontend)

The OCMOD file (`upload/install.xml`) patches:

1. **`catalog/controller/product/product.php`** — injects `price_match_url` and `price_match` variables into the product page data array.
2. **`catalog/view/theme/*/template/product/product.twig`** — adds a **"Price Match"** button after the product price, which opens a Bootstrap modal loaded via AJAX.

The modal form submits to `extension/module/price_match/send` which validates inputs and saves the request to the `oc_price_match` database table.

---

## Database

The module creates one table on install:

```sql
CREATE TABLE `oc_price_match` (
    `price_match_id`   INT(11)       NOT NULL AUTO_INCREMENT,
    `product_id`       INT(11)       NOT NULL DEFAULT '0',
    `customer_id`      INT(11)       NOT NULL DEFAULT '0',
    `firstname`        VARCHAR(32)   NOT NULL DEFAULT '',
    `lastname`         VARCHAR(32)   NOT NULL DEFAULT '',
    `email`            VARCHAR(96)   NOT NULL DEFAULT '',
    `telephone`        VARCHAR(32)   NOT NULL DEFAULT '',
    `competitor_name`  VARCHAR(255)  NOT NULL DEFAULT '',
    `competitor_url`   VARCHAR(512)  NOT NULL DEFAULT '',
    `competitor_price` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
    `comment`          TEXT          NOT NULL,
    `status`           TINYINT(1)    NOT NULL DEFAULT '0',
    `admin_comment`    TEXT          NOT NULL,
    `date_added`       DATETIME      NOT NULL,
    PRIMARY KEY (`price_match_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

The table is **dropped on uninstall**.

---

## File Structure

```
upload/
├── admin/
│   ├── controller/extension/module/price_match.php
│   ├── language/en-gb/extension/module/price_match.php
│   ├── model/extension/module/price_match.php
│   └── view/template/extension/module/
│       ├── price_match.twig          ← Module settings form
│       ├── price_match_list.twig     ← Request list
│       └── price_match_view.twig     ← Single request view/edit
├── catalog/
│   ├── controller/extension/module/price_match.php
│   ├── language/en-gb/extension/module/price_match.php
│   ├── model/extension/module/price_match.php
│   └── view/theme/default/template/extension/module/price_match.twig
└── install.xml   ← OCMOD patch file
```

---

## Uninstallation

1. Go to **Extensions → Extensions → Modules**, find **Price Match** and click **Uninstall**.
2. Go to **Extensions → Modifications** and click **Refresh**.
3. Optionally delete the uploaded files.

> **Warning:** Uninstalling will drop the `oc_price_match` table and all stored requests.

---

## Author

**iTech.bg** — [https://itech.bg](https://itech.bg)

## License

See [LICENSE](LICENSE) file.
