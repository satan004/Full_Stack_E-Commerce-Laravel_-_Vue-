<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Commerce') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <script defer src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    </head>
    <body class="store-body">
        <div id="customer-app" class="store-app" v-cloak>
            <header class="store-header">
                <div class="store-brand" v-on:click="setView('home')">
                    <strong>Commerce</strong>
                    <span>Laravel API + Vue storefront</span>
                </div>

                <nav class="store-nav">
                    <button v-for="item in navItems" :key="item.key" type="button" :class="{ active: view === item.key }" v-on:click="setView(item.key)">
                        <span v-text="item.label"></span>
                    </button>
                </nav>

                <div class="store-actions">
                    <button class="btn btn-muted" type="button" v-on:click="setView('cart')">
                        Cart <span v-text="cart.count || 0"></span>
                    </button>
                    <button v-if="!token" class="btn btn-primary" type="button" v-on:click="setView('auth')">Login</button>
                    <button v-else class="btn btn-muted" type="button" v-on:click="logout">Logout</button>
                    <a class="btn btn-muted" href="{{ route('admin.login') }}">Admin</a>
                </div>
            </header>

            <main class="store-main">
                <section class="store-summary">
                    <div>
                        <p class="eyebrow">Customer website</p>
                        <h1>Shop products, manage carts, checkout, and review orders.</h1>
                    </div>
                    <div class="summary-metrics">
                        <span><strong v-text="products.length"></strong> products loaded</span>
                        <span><strong v-text="categories.length"></strong> categories</span>
                        <span><strong v-text="orders.length"></strong> orders</span>
                    </div>
                </section>

                <div v-if="status" class="notice" v-text="status"></div>
                <div v-if="error" class="notice danger" v-text="error"></div>

                <section v-if="view === 'home' || view === 'products'" class="catalog-layout">
                    <form class="catalog-toolbar" v-on:submit.prevent="loadProducts">
                        <label>
                            Search
                            <input type="search" v-model="filters.search" placeholder="Search products">
                        </label>
                        <label>
                            Min
                            <input type="number" min="0" step="1" v-model="filters.min_price">
                        </label>
                        <label>
                            Max
                            <input type="number" min="0" step="1" v-model="filters.max_price">
                        </label>
                        <label>
                            Sort
                            <select v-model="filters.sort">
                                <option value="">Newest</option>
                                <option value="name">Name</option>
                                <option value="price_low">Price low</option>
                                <option value="price_high">Price high</option>
                            </select>
                        </label>
                        <button class="btn btn-primary" type="submit">Filter</button>
                    </form>

                    <div class="category-strip">
                        <button type="button" :class="{ active: !filters.category }" v-on:click="setCategory('')">All</button>
                        <button v-for="category in categories" :key="category.id" type="button" :class="{ active: filters.category === category.slug }" v-on:click="setCategory(category.slug)">
                            <span v-text="category.name"></span>
                            <small v-text="category.products_count"></small>
                        </button>
                    </div>

                    <div class="product-grid">
                        <article v-for="product in products" :key="product.id" class="product-card">
                            <button type="button" class="image-button" v-on:click="viewProduct(product)">
                                <img v-if="product.image_url" :src="product.image_url" :alt="product.name">
                                <span v-else>No image</span>
                            </button>
                            <div class="product-card-body">
                                <span class="badge" v-text="product.category?.name || 'Product'"></span>
                                <h2 v-text="product.name"></h2>
                                <p v-text="product.description"></p>
                                <div class="product-meta">
                                    <strong>${ price(product.price) }</strong>
                                    <span>${ ratingLabel(product) }</span>
                                </div>
                                <div class="product-actions">
                                    <button class="btn btn-primary" type="button" v-on:click="addToCart(product)">Add</button>
                                    <button class="btn btn-muted" type="button" v-on:click="toggleWishlist(product)">
                                        ${ isWishlisted(product.id) ? 'Saved' : 'Wishlist' }
                                    </button>
                                    <button class="btn btn-muted" type="button" v-on:click="viewProduct(product)">Details</button>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div v-if="!loading && !products.length" class="empty-state">No products found.</div>
                </section>

                <section v-if="view === 'detail' && selectedProduct" class="detail-layout">
                    <div class="detail-media">
                        <img v-if="selectedProduct.image_url" :src="selectedProduct.image_url" :alt="selectedProduct.name">
                    </div>
                    <div class="detail-panel stack">
                        <span class="badge" v-text="selectedProduct.category?.name"></span>
                        <h2 v-text="selectedProduct.name"></h2>
                        <p v-text="selectedProduct.description"></p>
                        <div class="product-meta">
                            <strong>${ price(selectedProduct.price) }</strong>
                            <span>${ ratingLabel(selectedProduct) }</span>
                            <span>${ selectedProduct.stock } in stock</span>
                        </div>
                        <div class="product-actions">
                            <button class="btn btn-primary" type="button" v-on:click="addToCart(selectedProduct)">Add to cart</button>
                            <button class="btn btn-muted" type="button" v-on:click="toggleWishlist(selectedProduct)">
                                ${ isWishlisted(selectedProduct.id) ? 'Saved' : 'Wishlist' }
                            </button>
                        </div>

                        <div class="review-box">
                            <h3>Reviews</h3>
                            <div v-if="selectedProduct.reviews?.length" class="review-list">
                                <article v-for="review in selectedProduct.reviews" :key="review.id">
                                    <strong>${ review.user?.name || 'Customer' } rated ${ review.rating }/5</strong>
                                    <p v-text="review.comment || 'No comment added.'"></p>
                                </article>
                            </div>
                            <p v-else>No reviews yet.</p>

                            <form v-if="token" class="review-form" v-on:submit.prevent="submitReview">
                                <select v-model="reviewForm.rating">
                                    <option value="5">5 stars</option>
                                    <option value="4">4 stars</option>
                                    <option value="3">3 stars</option>
                                    <option value="2">2 stars</option>
                                    <option value="1">1 star</option>
                                </select>
                                <textarea rows="3" v-model="reviewForm.comment" placeholder="Write a short review"></textarea>
                                <button class="btn btn-primary" type="submit">Submit Review</button>
                            </form>
                            <button v-else class="btn btn-muted" type="button" v-on:click="setView('auth')">Login to review</button>
                        </div>
                    </div>
                </section>

                <section v-if="view === 'auth'" class="auth-grid">
                    <form class="panel stack" v-on:submit.prevent="submitAuth">
                        <div class="panel-header">
                            <h2>${ authMode === 'login' ? 'Login' : 'Register' }</h2>
                            <button class="btn btn-muted" type="button" v-on:click="authMode = authMode === 'login' ? 'register' : 'login'">
                                ${ authMode === 'login' ? 'Create account' : 'Use login' }
                            </button>
                        </div>
                        <label v-if="authMode === 'register'">
                            Name
                            <input type="text" v-model="authForm.name">
                        </label>
                        <label>
                            Email
                            <input type="email" v-model="authForm.email">
                        </label>
                        <label>
                            Password
                            <input type="password" v-model="authForm.password">
                        </label>
                        <label v-if="authMode === 'register'">
                            Confirm Password
                            <input type="password" v-model="authForm.password_confirmation">
                        </label>
                        <button class="btn btn-primary" type="submit">${ authMode === 'login' ? 'Login' : 'Register' }</button>
                    </form>

                    <div class="panel stack">
                        <h2>Demo account</h2>
                        <p>Use <strong>customer@example.com</strong> with password <strong>password</strong> after running the seeder.</p>
                        <button class="btn btn-muted" type="button" v-on:click="fillDemoLogin">Fill Demo Login</button>
                    </div>
                </section>

                <section v-if="view === 'wishlist'" class="panel">
                    <div class="panel-header">
                        <h2>Wishlist</h2>
                    </div>
                    <div class="compact-list">
                        <article v-for="item in wishlist" :key="item.id">
                            <img v-if="item.product?.image_url" :src="item.product.image_url" :alt="item.product.name">
                            <div>
                                <strong v-text="item.product?.name"></strong>
                                <span>${ price(item.product?.price) }</span>
                            </div>
                            <button class="btn btn-primary" type="button" v-on:click="addToCart(item.product)">Add</button>
                            <button class="btn btn-muted" type="button" v-on:click="toggleWishlist(item.product)">Remove</button>
                        </article>
                    </div>
                    <div v-if="!wishlist.length" class="empty-state">No wishlist items yet.</div>
                </section>

                <section v-if="view === 'cart'" class="cart-layout">
                    <div class="panel">
                        <div class="panel-header">
                            <h2>Cart</h2>
                            <strong>${ price(cart.subtotal || 0) }</strong>
                        </div>
                        <div class="compact-list">
                            <article v-for="item in cart.items" :key="item.id">
                                <img v-if="item.product?.image_url" :src="item.product.image_url" :alt="item.product.name">
                                <div>
                                    <strong v-text="item.product?.name"></strong>
                                    <span>${ price(item.line_total) }</span>
                                </div>
                                <input class="qty-input" type="number" min="1" v-model.number="item.quantity" v-on:change="updateCart(item)">
                                <button class="btn btn-muted" type="button" v-on:click="removeCart(item)">Remove</button>
                            </article>
                        </div>
                        <div v-if="!cart.items?.length" class="empty-state">Your cart is empty.</div>
                    </div>

                    <form class="panel stack" v-on:submit.prevent="checkout">
                        <h2>Checkout</h2>
                        <label>
                            Shipping Address
                            <textarea rows="4" v-model="checkoutForm.shipping_address"></textarea>
                        </label>
                        <label>
                            Payment
                            <select v-model="checkoutForm.payment_method">
                                <option value="cash_on_delivery">Cash on delivery</option>
                                <option value="bank_transfer">Bank transfer</option>
                            </select>
                        </label>
                        <label>
                            Notes
                            <textarea rows="3" v-model="checkoutForm.notes"></textarea>
                        </label>
                        <button class="btn btn-primary" type="submit">Place Order</button>
                    </form>
                </section>

                <section v-if="view === 'orders'" class="panel">
                    <div class="panel-header">
                        <h2>Order History</h2>
                    </div>
                    <div class="order-list">
                        <article v-for="order in orders" :key="order.id">
                            <div>
                                <strong>Order #${ order.id }</strong>
                                <span class="badge" v-text="order.status"></span>
                                <p>${ order.items?.length || 0 } items - ${ price(order.total) }</p>
                            </div>
                            <ul>
                                <li v-for="item in order.items" :key="item.id">${ item.product_name } x ${ item.quantity }</li>
                            </ul>
                        </article>
                    </div>
                    <div v-if="!orders.length" class="empty-state">No orders yet.</div>
                </section>

                <section v-if="view === 'profile'" class="auth-grid">
                    <form class="panel stack" v-on:submit.prevent="updateProfile">
                        <h2>Profile</h2>
                        <label>
                            Name
                            <input type="text" v-model="profileForm.name">
                        </label>
                        <label>
                            Email
                            <input type="email" v-model="profileForm.email">
                        </label>
                        <label>
                            Phone
                            <input type="text" v-model="profileForm.phone">
                        </label>
                        <label>
                            Address
                            <textarea rows="3" v-model="profileForm.address"></textarea>
                        </label>
                        <button class="btn btn-primary" type="submit">Save Profile</button>
                    </form>

                    <form class="panel stack" v-on:submit.prevent="changePassword">
                        <h2>Change Password</h2>
                        <label>
                            Current Password
                            <input type="password" v-model="passwordForm.current_password">
                        </label>
                        <label>
                            New Password
                            <input type="password" v-model="passwordForm.password">
                        </label>
                        <label>
                            Confirm Password
                            <input type="password" v-model="passwordForm.password_confirmation">
                        </label>
                        <button class="btn btn-primary" type="submit">Change Password</button>
                    </form>
                </section>
            </main>
        </div>

        <script type="module">
            import { createApp } from 'https://unpkg.com/vue@3/dist/vue.esm-browser.prod.js';

            createApp({
                delimiters: ['${', '}'],
                data() {
                    return {
                        view: 'home',
                        loading: false,
                        status: '',
                        error: '',
                        token: localStorage.getItem('commerce_token') || '',
                        user: null,
                        categories: [],
                        products: [],
                        selectedProduct: null,
                        wishlist: [],
                        cart: { items: [], count: 0, subtotal: 0 },
                        orders: [],
                        filters: { search: '', category: '', min_price: '', max_price: '', sort: '' },
                        authMode: 'login',
                        authForm: { name: '', email: '', password: '', password_confirmation: '' },
                        profileForm: { name: '', email: '', phone: '', address: '' },
                        passwordForm: { current_password: '', password: '', password_confirmation: '' },
                        checkoutForm: { shipping_address: '', payment_method: 'cash_on_delivery', notes: '' },
                        reviewForm: { rating: '5', comment: '' },
                    };
                },
                computed: {
                    navItems() {
                        return [
                            { key: 'home', label: 'Products' },
                            { key: 'wishlist', label: 'Wishlist' },
                            { key: 'cart', label: 'Cart' },
                            { key: 'orders', label: 'Orders' },
                            { key: 'profile', label: 'Profile' },
                        ];
                    },
                },
                mounted() {
                    this.loadCatalog();

                    if (this.token) {
                        this.loadPrivateData();
                    }
                },
                methods: {
                    async request(config) {
                        this.error = '';
                        this.loading = true;

                        try {
                            const headers = {
                                Accept: 'application/json',
                                ...(this.token ? { Authorization: `Bearer ${this.token}` } : {}),
                            };
                            const response = await window.axios({ ...config, headers });

                            return response.data;
                        } catch (error) {
                            if (error.response?.status === 401) {
                                this.clearSession();
                            }

                            const errors = error.response?.data?.errors;
                            this.error = errors ? Object.values(errors).flat()[0] : (error.response?.data?.message || 'Request failed.');
                            throw error;
                        } finally {
                            this.loading = false;
                        }
                    },
                    async loadCatalog() {
                        await Promise.all([this.loadCategories(), this.loadProducts()]);
                    },
                    async loadCategories() {
                        this.categories = await this.request({ method: 'get', url: '/api/categories' });
                    },
                    async loadProducts() {
                        const params = Object.fromEntries(
                            Object.entries(this.filters).filter(([, value]) => value !== '' && value !== null),
                        );
                        const payload = await this.request({ method: 'get', url: '/api/products', params });
                        this.products = payload.data || payload;
                    },
                    async loadPrivateData() {
                        await Promise.allSettled([
                            this.loadProfile(),
                            this.loadCart(),
                            this.loadWishlist(),
                            this.loadOrders(),
                        ]);
                    },
                    async loadProfile() {
                        this.user = await this.request({ method: 'get', url: '/api/profile' });
                        this.profileForm = {
                            name: this.user.name || '',
                            email: this.user.email || '',
                            phone: this.user.phone || '',
                            address: this.user.address || '',
                        };
                        this.checkoutForm.shipping_address = this.user.address || this.checkoutForm.shipping_address;
                    },
                    async loadCart() {
                        this.cart = await this.request({ method: 'get', url: '/api/cart' });
                    },
                    async loadWishlist() {
                        this.wishlist = await this.request({ method: 'get', url: '/api/wishlist' });
                    },
                    async loadOrders() {
                        this.orders = await this.request({ method: 'get', url: '/api/orders' });
                    },
                    setCategory(slug) {
                        this.filters.category = slug;
                        this.loadProducts();
                    },
                    setView(nextView) {
                        if (['wishlist', 'cart', 'orders', 'profile'].includes(nextView) && !this.token) {
                            this.status = 'Please login to continue.';
                            this.view = 'auth';
                            return;
                        }

                        this.status = '';
                        this.view = nextView;
                    },
                    async viewProduct(product) {
                        this.selectedProduct = await this.request({ method: 'get', url: `/api/products/${product.id}` });
                        this.view = 'detail';
                    },
                    async submitAuth() {
                        const url = this.authMode === 'login' ? '/api/login' : '/api/register';
                        const payload = await this.request({ method: 'post', url, data: this.authForm });
                        this.token = payload.token;
                        this.user = payload.user;
                        localStorage.setItem('commerce_token', this.token);
                        this.status = 'You are logged in.';
                        await this.loadPrivateData();
                        this.view = 'home';
                    },
                    async logout() {
                        if (this.token) {
                            await this.request({ method: 'post', url: '/api/logout' }).catch(() => {});
                        }

                        this.clearSession();
                        this.status = 'Logged out.';
                        this.view = 'home';
                    },
                    clearSession() {
                        localStorage.removeItem('commerce_token');
                        this.token = '';
                        this.user = null;
                        this.wishlist = [];
                        this.cart = { items: [], count: 0, subtotal: 0 };
                        this.orders = [];
                    },
                    fillDemoLogin() {
                        this.authMode = 'login';
                        this.authForm.email = 'customer@example.com';
                        this.authForm.password = 'password';
                    },
                    async addToCart(product) {
                        if (!this.token) {
                            this.setView('auth');
                            return;
                        }

                        this.cart = await this.request({
                            method: 'post',
                            url: '/api/cart',
                            data: { product_id: product.id, quantity: 1 },
                        });
                        this.status = 'Added to cart.';
                    },
                    async updateCart(item) {
                        this.cart = await this.request({
                            method: 'put',
                            url: `/api/cart/${item.id}`,
                            data: { quantity: item.quantity },
                        });
                    },
                    async removeCart(item) {
                        this.cart = await this.request({ method: 'delete', url: `/api/cart/${item.id}` });
                    },
                    isWishlisted(productId) {
                        return this.wishlist.some((item) => item.product_id === productId);
                    },
                    async toggleWishlist(product) {
                        if (!this.token) {
                            this.setView('auth');
                            return;
                        }

                        if (this.isWishlisted(product.id)) {
                            await this.request({ method: 'delete', url: `/api/wishlist/${product.id}` });
                        } else {
                            await this.request({ method: 'post', url: '/api/wishlist', data: { product_id: product.id } });
                        }

                        await this.loadWishlist();
                    },
                    async checkout() {
                        await this.request({ method: 'post', url: '/api/checkout', data: this.checkoutForm });
                        this.status = 'Order created.';
                        this.checkoutForm.notes = '';
                        await Promise.all([this.loadCart(), this.loadOrders(), this.loadProducts()]);
                        this.view = 'orders';
                    },
                    async updateProfile() {
                        this.user = await this.request({ method: 'put', url: '/api/profile', data: this.profileForm });
                        this.status = 'Profile updated.';
                    },
                    async changePassword() {
                        await this.request({ method: 'put', url: '/api/profile/password', data: this.passwordForm });
                        this.passwordForm = { current_password: '', password: '', password_confirmation: '' };
                        this.status = 'Password changed.';
                    },
                    async submitReview() {
                        this.selectedProduct = await this.request({
                            method: 'post',
                            url: `/api/products/${this.selectedProduct.id}/reviews`,
                            data: this.reviewForm,
                        });
                        this.reviewForm.comment = '';
                        this.status = 'Review saved.';
                    },
                    price(value) {
                        return new Intl.NumberFormat('en-US', {
                            style: 'currency',
                            currency: 'USD',
                        }).format(Number(value || 0));
                    },
                    ratingLabel(product) {
                        const rating = product.reviews_avg_rating;
                        return rating ? `${Number(rating).toFixed(1)} rating` : 'New';
                    },
                },
            }).mount('#customer-app');
        </script>
    </body>
</html>
