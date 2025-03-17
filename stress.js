import http from 'k6/http';
import { check, sleep } from 'k6';

const USER_COUNT = 50;

export let options = {
    vus: USER_COUNT,
    iterations: USER_COUNT,
};

export default function () {
    let baseUrl = 'http://localhost:8088/api';

    let registerPayload = JSON.stringify({
        name: `user${__VU}`,
        email: `user${__VU}@test.com`,
        password: 'password123',
        password_confirmation: 'password123',
    });

    let registerHeaders = { 'Content-Type': 'application/json' };

    let registerRes = http.post(`${baseUrl}/register`, registerPayload, { headers: registerHeaders });

    check(registerRes, {
        'Регистрация успешна': (res) => res.status === 200,
    });

    let token;
    if (registerRes.status === 200) {
        let loginPayload = JSON.stringify({
            email: `user${__VU}@test.com`,
            password: 'password123',
        });

        let loginRes = http.post(`${baseUrl}/login`, loginPayload, { headers: registerHeaders });

        check(loginRes, {
            'Логин успешен': (res) => res.status === 200,
        });

        if (loginRes.status === 200) {
            token = JSON.parse(loginRes.body).token;
        }
    }

    if (token) {
        let authHeaders = { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json',  'Origin': 'http://localhost:5174/', };

        let firstRes = http.post(`${baseUrl}/purchase-ticket/5`,null, { headers: authHeaders });

        let responseData = JSON.parse(firstRes.body);

        check(firstRes, {
            'Запрос на покупку': (res) => res.status === 200,
        });

        let confirmPayment = JSON.stringify({
            status: 'paid',
        });

        let secondRes = http.post(`${baseUrl}/confirm-payment/ticket-order/${responseData.order_id}`, confirmPayment, { headers: authHeaders });

        check(secondRes, {
            'Редирект обратно на сайт с платежного шлюза': (res) => res.status === 200,
        });
    }

    sleep(0.3);
}

