    // ── Lecture de la pièce (OCR local : l'image ne quitte pas l'appareil pour cette étape) ──
    // 1) Bande MRZ (passeport, dos des cartes d'identité) : numéro, naissance, expiration, nationalité,
    //    validés par les chiffres de contrôle ICAO 9303 — une lecture douteuse n'est jamais utilisée.
    // 2) Sinon : dates imprimées ; la plus lointaine dans le futur est la date d'expiration.
    var ocrP = null, ocrHint = document.getElementById('gr-ocr'), expHint = document.getElementById('gr-ocr-exp');
    var numIn = form.elements.document_number, expIn = form.elements.document_expiry_date;
    function loadOcr() { if (!ocrP && window.Tesseract) ocrP = Tesseract.createWorker('eng').then(function (w) { return w.setParameters({ tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789<./- ', tessedit_pageseg_mode: '11' }).then(function () { return w; }); }).catch(function () { return null; }); }
    var MRZ = window.TivMrz = {
        check: function (s) { var w = [7, 3, 1], t = 0; for (var i = 0; i < s.length; i++) { var c = s[i], v = c === '<' ? 0 : /\d/.test(c) ? +c : c.charCodeAt(0) - 55; t += v * w[i % 3]; } return String(t % 10); },
        digits: function (s) { return s.replace(/O/g, '0').replace(/[IL]/g, '1').replace(/S/g, '5').replace(/B/g, '8'); },
        date: function (yymmdd, future) {
            if (!/^\d{6}$/.test(yymmdd)) return null;
            var yy = +yymmdd.slice(0, 2), cur = new Date().getFullYear() % 100, year = future ? 2000 + yy : (yy > cur ? 1900 + yy : 2000 + yy);
            var m = yymmdd.slice(2, 4), d = yymmdd.slice(4, 6); if (+m < 1 || +m > 12 || +d < 1 || +d > 31) return null;
            return year + '-' + m + '-' + d;
        },
        field: function (raw, cd) { var v = this.digits(raw); return this.check(v) === this.digits(cd) ? v : null; },
        parse: function (text) {
            var lines = (text || '').toUpperCase().split(/\n/).map(function (l) { return l.replace(/\s+/g, '').replace(/[«‹]/g, '<'); }).filter(function (l) { return l.length >= 28 && (l.match(/</g) || []).length >= 2; });
            for (var i = 0; i < lines.length; i++) {
                var a = lines[i], b = lines[i + 1] || '';
                // Passeport (TD3) : 2 lignes de 44, la première commence par « P ».
                if (a[0] === 'P' && b.length >= 43) {
                    b = b.slice(0, 44);
                    var num = b.slice(0, 9), birth = this.field(b.slice(13, 19), b[19]), exp = this.field(b.slice(21, 27), b[27]);
                    var okNum = this.check(num) === this.digits(b[9]);
                    if (exp) return { number: okNum ? num.replace(/</g, '') : null, expiry: this.date(exp, true), birth: birth && this.date(birth, false), nationality: b.slice(10, 13).replace(/</g, '') };
                }
                // Carte d'identité (TD1) : 3 lignes de 30 ; numéro en ligne 1, dates en ligne 2.
                if (/^[IAC][A-Z<]/.test(a) && b.length >= 29) {
                    var n1 = a.slice(5, 14), cdn = a[14], b2 = b.slice(0, 30);
                    var birth1 = this.field(b2.slice(0, 6), b2[6]), exp1 = this.field(b2.slice(8, 14), b2[14]);
                    if (exp1) return { number: this.check(n1) === this.digits(cdn) ? n1.replace(/</g, '') : null, expiry: this.date(exp1, true), birth: birth1 && this.date(birth1, false), nationality: b2.slice(15, 18).replace(/</g, '') };
                }
            }
            return null;
        },
        // Dates imprimées « 12/05/2031 », « 12.05.2031 », « 12-05-2031 » : la plus lointaine encore à venir.
        printedExpiry: function (text) {
            var best = null, today = new Date().toISOString().slice(0, 10);
            (text || '').replace(/\b(\d{2})[./-](\d{2})[./-](\d{4})\b/g, function (_, d, m, y) {
                if (+m < 1 || +m > 12 || +d < 1 || +d > 31) return;
                var iso = y + '-' + m + '-' + d; if (iso > today && (!best || iso > best)) best = iso;
            });
            return best;
        },
        // Numéro probable hors MRZ : jeton de 5 à 15 caractères riche en chiffres (jamais une ligne MRZ entière).
        printedNumber: function (text) {
            var best = null, score = -1;
            (text || '').split(/[^A-Za-z0-9]+/).forEach(function (t) {
                if (t.length < 5 || t.length > 15) return;
                var d = (t.match(/\d/g) || []).length; if (d < 2) return;
                var sc = d + (d / t.length) * 3; if (sc > score) { score = sc; best = t.toUpperCase(); }
            });
            return best;
        },
    };
    var NATIONALITIES = { CIV: 'Ivoirienne', BFA: 'Burkinabè', MLI: 'Malienne', GIN: 'Guinéenne', SEN: 'Sénégalaise', GHA: 'Ghanéenne', NGA: 'Nigériane', TGO: 'Togolaise', BEN: 'Béninoise', NER: 'Nigérienne', LBR: 'Libérienne', CMR: 'Camerounaise', MAR: 'Marocaine', LBN: 'Libanaise', FRA: 'Française', BEL: 'Belge', CHE: 'Suisse', DEU: 'Allemande', D: 'Allemande', GBR: 'Britannique', USA: 'Américaine', CAN: 'Canadienne', CHN: 'Chinoise' };
    function fillIfEmpty(el, value) { if (el && value && !el.value.trim()) { el.value = value; el.dispatchEvent(new Event('input', { bubbles: true })); return true; } return false; }
    function readDocument(file) {
        if (!file || (numIn.value.trim() && expIn.value)) return;
        loadOcr(); if (!ocrP) return;
        ocrHint.hidden = false; ocrHint.textContent = T.ocrRun;
        ocrP.then(function (w) { return w ? w.recognize(file) : null; }).then(function (r) {
            var text = r && r.data && r.data.text, mrz = MRZ.parse(text), gotNum = false, gotExp = false;
            if (mrz) {
                gotNum = fillIfEmpty(numIn, mrz.number);
                gotExp = fillIfEmpty(expIn, mrz.expiry);
                fillIfEmpty(form.elements.birth_date, mrz.birth);
                if (mrz.nationality && NATIONALITIES[mrz.nationality]) {
                    var nat = form.elements.nationality; if (nat && (!nat.value.trim() || nat.value === 'Ivoirienne')) nat.value = NATIONALITIES[mrz.nationality];
                }
            }
            if (!numIn.value.trim()) gotNum = fillIfEmpty(numIn, MRZ.printedNumber(text));
            if (!expIn.value) gotExp = fillIfEmpty(expIn, MRZ.printedExpiry(text));
            ocrHint.textContent = numIn.value.trim() ? (gotNum ? T.ocrOk : '') : T.ocrKo;
            ocrHint.hidden = !ocrHint.textContent;
            if (gotExp) { expHint.textContent = T.expOk; expHint.hidden = false; }
            else if (!expIn.value) { expHint.textContent = T.expKo; expHint.hidden = false; }
            saveDraft();
        }).catch(function () { ocrHint.hidden = true; });
    }
