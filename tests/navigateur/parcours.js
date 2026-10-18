#!/usr/bin/env node
/**
 * Parcours navigateur du backoffice de Ploopi (Playwright, Chromium).
 *
 *   tests/instance.sh navigateur [série]      sur l'instance montée
 *   tests/instance.sh test --navigateur       suite complète, puis ce parcours
 *
 * Se connecte en administrateur et parcourt, par le menu latéral de ploopi4, chaque
 * écran de « Mon espace » puis de l'espace de travail qui porte les modules
 * (administration de system et chaque module), et chacun de leurs onglets. Puis
 * éprouve les fenêtres (dbreport, planning, aide du wiki) et les éditeurs (TinyMCE
 * dans news2, jsToolbar dans le wiki).
 *
 * Trois passes (PLOOPI_NAV_PASSES) : clair et sombre (1440 px, thème du système
 * simulé, que ploopi4 suit par défaut), téléphone (390 px). Pour chaque écran : une
 * capture et les problèmes relevés — réponse HTTP en erreur, ressource introuvable
 * (404…), erreur JavaScript, erreur PHP (journal de Ploopi et du serveur, message
 * affiché), thème non appliqué, débordement horizontal sur téléphone.
 *
 * Résultat dans PLOOPI_NAV_DOSSIER (tests/instance.sh : tests/resultats/<version de
 * PHP>/navigateur/), à défaut tests/navigateur/captures/<série>/, non versionnés : une
 * capture par écran et par passe, rapport.md, et resultats.json que tests/rapport.php
 * reprend dans junit.xml et rapport.html. Code retour 1 si un écran a un problème.
 *
 * Aucune action d'écriture : les liens de suppression, désinstallation, réindexation,
 * déconnexion… et tout lien qui demande confirmation sont ignorés ; les fenêtres et
 * formulaires sont ouverts puis abandonnés.
 *
 * Variables (renseignées par tests/instance.sh) : PLOOPI_NAV_URL, PLOOPI_NAV_LOGIN,
 * PLOOPI_NAV_MDP, PLOOPI_NAV_JOURNAL (journal d'erreurs de Ploopi),
 * PLOOPI_NAV_JOURNAL_SERVEUR (journal du serveur PHP), PLOOPI_NAV_PREPARATION (JSON
 * de tests/navigateur/preparer.php), PLOOPI_NAV_PASSES (clair,sombre,telephone),
 * PLOOPI_NAV_DOSSIER (dossier de résultats, vidé au départ).
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

'use strict';

const fs = require('fs');
const path = require('path');
let chromium;
try {
    ({ chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright'));
} catch (e) {
    console.error('Module Node « playwright » introuvable : npm install -g playwright && npx playwright install chromium\n'
        + '(tests/instance.sh le cherche dans $(npm root -g) ; sinon PLAYWRIGHT_MODULE=<chemin du module>).');
    process.exit(2);
}

const RACINE = path.resolve(__dirname, '..', '..');
const BASE = process.env.PLOOPI_NAV_URL || 'http://127.0.0.1:8099';
const LOGIN = process.env.PLOOPI_NAV_LOGIN || 'admin';
const MDP = process.env.PLOOPI_NAV_MDP || 'Adm1n!Test2026';
const JOURNAUX = [process.env.PLOOPI_NAV_JOURNAL, process.env.PLOOPI_NAV_JOURNAL_SERVEUR].filter(Boolean);
const PREPARATION = JSON.parse(process.env.PLOOPI_NAV_PREPARATION || '{"espace":2,"modules":{}}');

const PASSES = {
    clair: { largeur: 1440, hauteur: 900, theme: 'light' },
    sombre: { largeur: 1440, hauteur: 900, theme: 'dark' },
    telephone: { largeur: 390, hauteur: 844, theme: 'light', mobile: true },
};
const NOMS_PASSES = (process.env.PLOOPI_NAV_PASSES || 'clair,sombre,telephone').split(/[ ,]+/).filter(p => PASSES[p]);

const horodatage = new Date().toISOString().replace(/[-:]/g, '').replace('T', '_').slice(0, 15);
const SERIE = process.argv[2] || horodatage;
const DOSSIER = process.env.PLOOPI_NAV_DOSSIER ? path.resolve(process.env.PLOOPI_NAV_DOSSIER) : path.join(RACINE, 'tests', 'navigateur', 'captures', SERIE);

// Libellés de liens jamais suivis : actions, pas écrans
const DESTRUCTIF = /supprim|désinstall|desinstall|vider|réinitialis|reinitialis|réindex|reindex|déconnex|purge|effacer|sauvegarde|dump|mettre à jour|mise à jour|import/i;

/* ------------------------------------------------------------------ */
/* Journaux d'erreurs PHP                                              */

function tailles() {
    return JOURNAUX.map(f => { try { return fs.statSync(f).size; } catch (e) { return 0; } });
}

// Messages écrits dans les journaux depuis les positions données
function messagesJournaux(depuis) {
    const res = [];
    JOURNAUX.forEach((f, i) => {
        let texte = '';
        try {
            const fd = fs.openSync(f, 'r');
            const taille = fs.fstatSync(fd).size - depuis[i];
            if (taille > 0) {
                const tampon = Buffer.alloc(taille);
                fs.readSync(fd, tampon, 0, taille, depuis[i]);
                texte = tampon.toString('utf8');
            }
            fs.closeSync(fd);
        } catch (e) { return; }
        // Journal de Ploopi (ploopi\error) puis journal du serveur PHP intégré
        for (const m of texte.matchAll(/Type d'erreur : (.*)\nMessage : (.*)(?:\nLieu : (.*))?/g)) {
            res.push(`${m[1].trim()} : ${m[2].trim()}${m[3] ? ` (${m[3].trim().replace(RACINE + '/', '')})` : ''}`);
        }
        for (const m of texte.matchAll(/PHP (Fatal error|Parse error|Warning|Notice|Deprecated):\s*([^\n]{0,240})/g)) {
            res.push(`${m[1]} : ${m[2].replace(RACINE + '/', '')}`);
        }
    });
    return [...new Set(res)];
}

// Messages PHP affichés dans la page, page d'erreur de Ploopi, jeton refusé
function messagesPage(texte) {
    const motifs = [/(Fatal error|Parse error|Warning|Notice|Deprecated)\s*:[^\n]{0,200}/g,
        /Une erreur est survenue sur le site[^\n]{0,80}/g, /Jeton (absent|non valide)[^\n]{0,80}/g];
    const res = [];
    for (const re of motifs) for (const m of texte.matchAll(re)) res.push(m[0].trim());
    return [...new Set(res)];
}

/* ------------------------------------------------------------------ */
/* Navigation                                                          */

// Lecture dans la page, tolérante à une navigation en cours (redirection par script)
async function evaluer(page, fn, arg) {
    for (let i = 0; ; i++) {
        try {
            await page.waitForLoadState('load');
            return await page.evaluate(fn, arg);
        } catch (e) {
            if (i >= 4 || !/context was destroyed|navigat/i.test(e.message)) throw e;
            await page.waitForTimeout(500);
        }
    }
}

async function connecter(page) {
    await page.goto(`${BASE}/admin.php`);
    await page.fill('input[name=ploopi_login]', LOGIN);
    await page.fill('input[name=ploopi_password]', MDP);
    await Promise.all([page.waitForNavigation(), page.press('input[name=ploopi_password]', 'Enter')]);
    if (!await evaluer(page, () => window._PLOOPI_ENV || '')) throw new Error(`connexion impossible pour ${LOGIN}`);
}

/**
 * URL en clair accompagnée de ploopi_env (menu / espace / module / action / jeton
 * de la page courante), que le socle accepte comme une URL chiffrée.
 */
async function urlClaire(page, intMenu, intModule, params = {}) {
    const env = await evaluer(page, () => window._PLOOPI_ENV || '');
    const query = new URLSearchParams({ ploopi_env: `${intMenu}/${PREPARATION.espace}/${intModule}/public/${env.split('/')[4] || ''}`, ...params });
    return `${BASE}/admin.php?${query}`;
}

// Entrées du menu latéral : [{ module, sous }], sous = '' pour l'entrée du module
async function menuLateral(page) {
    return evaluer(page, () => {
        const texte = a => (a.querySelector('span') || a).textContent.replace(/\s+/g, ' ').trim();
        const res = [];
        for (const m of document.querySelectorAll('nav.p4-nav .p4-nav-module')) {
            const a = m.querySelector(':scope > a');
            if (!a) continue;
            res.push({ module: texte(a), sous: '' });
            for (const s of m.querySelectorAll('.p4-nav-sub > a')) res.push({ module: texte(a), sous: texte(s) });
        }
        return res;
    });
}

// Adresse d'une entrée du menu, ou d'un onglet, lue sur la page courante (jeton frais)
async function adresse(page, entree) {
    return evaluer(page, e => {
        const texte = a => (a.querySelector('span') || a).textContent.replace(/\s+/g, ' ').trim();
        const sur = a => a && !a.hasAttribute('data-ploopi-confirm') && !a.classList.contains('p4-danger')
            && (!a.target || a.target === '_self') && /^admin\.php\?/.test(a.getAttribute('href') || '') ? a.href : '';
        if (e.onglet) return sur([...document.querySelectorAll('a.p4-tab, .tabs a')].find(a => texte(a) === e.onglet));
        const m = [...document.querySelectorAll('nav.p4-nav .p4-nav-module')].find(m => m.querySelector(':scope > a') && texte(m.querySelector(':scope > a')) === e.module);
        if (!m) return '';
        return sur(e.sous ? [...m.querySelectorAll('.p4-nav-sub > a')].find(a => texte(a) === e.sous) : m.querySelector(':scope > a'));
    }, entree);
}

async function onglets(page) {
    return evaluer(page, () => [...document.querySelectorAll('a.p4-tab:not(.p4-active), .tabs a:not(.selected)')]
        .map(a => (a.querySelector('span') || a).textContent.replace(/\s+/g, ' ').trim()).filter(Boolean));
}

/* ------------------------------------------------------------------ */
/* Écrans                                                              */

class Passe {
    constructor(nom, page) {
        this.nom = nom;
        this.reglage = PASSES[nom];
        this.page = page;
        this.ecrans = [];
        this.js = [];
        this.ressources = [];
        page.on('console', m => { if (m.type() === 'error' && !/^Failed to load resource/.test(m.text())) this.js.push(m.text().slice(0, 240)); });
        page.on('pageerror', e => this.js.push(String(e.message || e).slice(0, 240)));
        page.on('response', r => { if (r.status() >= 400) this.ressources.push(`HTTP ${r.status()} : ${r.url().replace(BASE, '').slice(0, 160)}`); });
        page.on('dialog', d => d.dismiss());
        fs.mkdirSync(path.join(DOSSIER, nom), { recursive: true });
    }

    /**
     * Ouvre un écran par action(page), puis relève ses problèmes et le capture.
     * verifier(page), avant la capture, et apres(page), après, peuvent ajouter des
     * contrôles propres à l'écran (tableau de messages).
     */
    async ecran(libelle, action, verifier, apres) {
        const page = this.page;
        this.js = [];
        this.ressources = [];
        const avant = tailles();
        const problemes = [];
        let code = 0;
        try {
            const rep = await action(page);
            if (rep && typeof rep.status === 'function') code = rep.status();
            await page.waitForLoadState('load');
            await page.waitForTimeout(400);
            if (verifier) problemes.push(...(await verifier(page)));
        } catch (e) {
            problemes.push(`navigation : ${e.message.split('\n')[0]}`);
        }
        if (code >= 400) problemes.push(`réponse HTTP ${code}`);

        const nom = `${String(this.ecrans.length + 1).padStart(3, '0')}_${libelle.normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-zA-Z0-9]+/g, '_').replace(/^_|_$/g, '').toLowerCase().slice(0, 60)}`;
        try { await this.capture(path.join(DOSSIER, this.nom, `${nom}.png`)); } catch (e) { problemes.push(`capture : ${e.message.split('\n')[0]}`); }
        if (apres) {
            try { problemes.push(...(await apres(page))); } catch (e) { problemes.push(e.message.split('\n')[0]); }
        }

        try {
            problemes.push(...messagesPage(await evaluer(page, () => document.body ? document.body.innerText : '')));
            problemes.push(...(await this.mise_en_page()));
        } catch (e) {
            problemes.push(`lecture de la page : ${e.message.split('\n')[0]}`);
        }
        problemes.push(...messagesJournaux(avant).map(m => `PHP : ${m}`));
        problemes.push(...[...new Set(this.js)].map(m => `JavaScript : ${m}`));
        problemes.push(...[...new Set(this.ressources)]);

        this.ecrans.push({ nom, libelle, problemes: [...new Set(problemes)] });
        process.stdout.write(`${problemes.length ? '!' : '.'} ${this.nom}/${nom}\n`);
    }

    // Thème appliqué (fond clair ou sombre), débordement horizontal
    async mise_en_page() {
        const theme = this.reglage.theme;
        const mesure = await evaluer(this.page, () => {
            const p4 = document.body && document.body.classList.contains('p4-body');
            const zone = document.getElementById('p4-main') || document.body;
            const fond = zone ? getComputedStyle(zone).backgroundColor : '';
            return { p4, fond, largeur: document.documentElement.scrollWidth, vue: window.innerWidth };
        });
        const res = [];
        if (mesure.p4) {
            const c = (mesure.fond.match(/[\d.]+/g) || []).map(Number);
            if (c.length >= 3 && (c.length < 4 || c[3] > 0)) {
                const lum = (0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2]) / 255;
                if (theme === 'dark' && lum > 0.4) res.push(`Thème sombre non appliqué (fond ${mesure.fond})`);
                if (theme === 'light' && lum < 0.6) res.push(`Thème clair non appliqué (fond ${mesure.fond})`);
            }
        }
        if (this.reglage.mobile && mesure.largeur > mesure.vue + 1) {
            res.push(`Débordement horizontal : page de ${mesure.largeur} px pour un écran de ${mesure.vue} px`);
        }
        return res;
    }

    /**
     * Capture de toute la page. Sous ploopi4, le contenu défile dans #pagecontent sous
     * une barre et un menu fixes : la fenêtre est agrandie à la hauteur de ce contenu le
     * temps de la capture (8000 px au plus).
     */
    async capture(fichier) {
        const page = this.page;
        const { largeur, hauteur } = this.reglage;
        const intHauteur = await evaluer(page, () => {
            const zone = document.getElementById('pagecontent');
            if (!zone || !document.body.classList.contains('p4-body')) return 0;
            return zone.getBoundingClientRect().top + zone.scrollHeight;
        });
        if (intHauteur > hauteur) {
            await page.setViewportSize({ width: largeur, height: Math.min(Math.ceil(intHauteur), 8000) });
            await page.waitForTimeout(300);
        }
        await page.screenshot({ path: fichier, fullPage: true });
        if (intHauteur > hauteur) await page.setViewportSize({ width: largeur, height: hauteur });
    }
}

/* ------------------------------------------------------------------ */
/* Parcours                                                            */

/**
 * Parcourt le menu latéral d'un point d'entrée (« Mon espace », espace de travail) :
 * chaque entrée, puis chaque onglet des écrans atteints (un niveau).
 */
async function parcourirMenu(passe, prefixe, entree) {
    const page = passe.page;
    await page.goto(await entree(page));
    const vus = new Set();
    for (const e of await menuLateral(page)) {
        if (DESTRUCTIF.test(e.sous || e.module)) continue;
        let href = await adresse(page, e);
        if (!href) { await page.goto(await entree(page)); href = await adresse(page, e); }
        if (!href) continue;
        const libelle = `${prefixe} › ${e.module}${e.sous ? ` › ${e.sous}` : ''}`;
        await passe.ecran(libelle, p => p.goto(href));

        for (const o of await onglets(page)) {
            const cle = `${e.module} › ${o}`;
            if (vus.has(cle) || DESTRUCTIF.test(o)) continue;
            vus.add(cle);
            const hrefOnglet = await adresse(page, { onglet: o });
            if (hrefOnglet) await passe.ecran(`${libelle} › ${o}`, p => p.goto(hrefOnglet));
        }
    }
}

// Fenêtre ouverte par un déclencheur : visible, dans l'écran, déplaçable, refermable
async function fenetre(passe, libelle, intModule, declencheur, id, params = {}) {
    if (!intModule) return;
    await passe.ecran(libelle, async p => {
        const rep = await p.goto(await urlClaire(p, 1, intModule, params));
        await p.click(declencheur, { timeout: 10000 });
        await p.waitForSelector(`#${id}`, { state: 'visible', timeout: 15000 });
        await p.waitForTimeout(500);
        return rep;
    }, async p => {
        const res = [];
        const boite = await p.locator(`#${id}`).boundingBox();
        const vue = p.viewportSize();
        if (!boite) return [`fenêtre #${id} sans dimensions`];
        if (boite.x < -1 || boite.x + boite.width > vue.width + 1) res.push(`fenêtre #${id} hors de l'écran (x ${Math.round(boite.x)}, largeur ${Math.round(boite.width)} pour ${vue.width} px)`);
        const poignee = p.locator(`#${id} .handle_${id}`).first();
        if (!passe.reglage.mobile && await poignee.count()) {
            const t = await poignee.boundingBox();
            await p.mouse.move(t.x + 20, t.y + t.height / 2);
            await p.mouse.down();
            await p.mouse.move(t.x - 40, t.y + t.height / 2 + 60, { steps: 8 });
            await p.mouse.up();
            const apres = await p.locator(`#${id}`).boundingBox();
            if (!apres || (Math.abs(apres.x - boite.x) < 5 && Math.abs(apres.y - boite.y) < 5)) res.push(`fenêtre #${id} non déplaçable par son titre`);
        }
        return res;
    }, async p => {
        const res = [];
        const fermer = p.locator(`#close_${id}, #${id} [data-ploopi-popup-hide]`).first();
        if (await fermer.count()) {
            await fermer.click();
            // Fermeture en fondu
            try { await p.waitForSelector(`#${id}`, { state: 'hidden', timeout: 3000 }); } catch (e) { res.push(`fenêtre #${id} non refermée par son bouton`); }
        } else {
            res.push(`fenêtre #${id} sans bouton de fermeture`);
        }
        return res;
    });
}

async function editeurs(passe) {
    const mod = PREPARATION.modules;

    // TinyMCE : rédaction d'une news (data-ploopi-editor, js/editor.js)
    if (mod.news2) await passe.ecran('Éditeur TinyMCE (news2 › rédiger)', async p => p.goto(await urlClaire(p, 1, mod.news2, { entity: 'admin', action: 'write' })), async p => {
        try {
            await p.waitForSelector('.tox-tinymce', { state: 'visible', timeout: 20000 });
        } catch (e) {
            return ['TinyMCE ne s\'est pas initialisé (.tox-tinymce absent)'];
        }
        const texte = await p.evaluate(() => {
            const ed = window.tinymce && window.tinymce.activeEditor;
            if (!ed) return null;
            ed.setContent('<p>Essai du parcours navigateur</p>');
            return ed.getContent();
        });
        return /Essai du parcours navigateur/.test(texte || '') ? [] : ['TinyMCE : contenu non modifiable par son API'];
    });

    // jsToolbar : modification d'une page du wiki (lib/jstoolbar du module)
    if (mod.wiki) {
        await passe.ecran('Éditeur jsToolbar (wiki › modifier une page)', async p => p.goto(await urlClaire(p, 1, mod.wiki, { op: 'wiki_page_modify', wiki_page_id: 'ParcoursNavigateur' })), async p => {
            try {
                await p.waitForSelector('.jstElements button', { state: 'visible', timeout: 10000 });
            } catch (e) {
                return ['jsToolbar absente (.jstElements)'];
            }
            const res = [];
            const libelles = await p.evaluate(() => [...document.querySelectorAll('.jstElements button')].map(b => b.title));
            if (!libelles.includes('Gras')) res.push(`jsToolbar non traduite en français (boutons : ${libelles.slice(0, 4).join(', ')})`);
            await p.fill('#wiki_page_content', '');
            await p.click('.jstElements button.jstb_strong');
            const valeur = await p.inputValue('#wiki_page_content');
            if (!valeur.includes('*')) res.push(`bouton Gras sans effet (contenu : « ${valeur} »)`);
            return res;
        });
        await fenetre(passe, 'Fenêtre : aide du wiki', mod.wiki, 'a[data-ploopi-topopup="popup_wiki_help"]', 'popup_wiki_help', { op: 'wiki_page_modify', wiki_page_id: 'ParcoursNavigateur' });
    }
}

/* ------------------------------------------------------------------ */

(async () => {
    if (process.env.PLOOPI_NAV_DOSSIER) fs.rmSync(DOSSIER, { recursive: true, force: true });
    fs.mkdirSync(DOSSIER, { recursive: true });
    const navigateur = await chromium.launch({ ignoreDefaultArgs: ['--hide-scrollbars'] });
    const passes = [];

    for (const nom of NOMS_PASSES) {
        const r = PASSES[nom];
        const contexte = await navigateur.newContext({
            viewport: { width: r.largeur, height: r.hauteur }, colorScheme: r.theme, locale: 'fr-FR',
            isMobile: !!r.mobile, hasTouch: !!r.mobile,
        });
        const page = await contexte.newPage();
        const passe = new Passe(nom, page);
        passes.push(passe);
        console.log(`\n── passe ${nom} (${r.largeur} px, thème ${r.theme === 'dark' ? 'sombre' : 'clair'})`);

        await passe.ecran('Connexion', p => p.goto(`${BASE}/admin.php`));
        await connecter(page);
        const accueil = page.url();

        await parcourirMenu(passe, 'Mon espace', async () => accueil);
        await parcourirMenu(passe, 'Espace', async p => urlClaire(p, 1, 1, {}));

        await fenetre(passe, 'Fenêtre : nouvelle requête (dbreport)', PREPARATION.modules.dbreport, '[data-ploopi-topopup="dbreport_query_add"]', 'dbreport_query_add');
        await fenetre(passe, 'Fenêtre : nouvel événement (planning)', PREPARATION.modules.planning, '[data-ploopi-topopup="popup_planning_event"]', 'popup_planning_event');
        await editeurs(passe);

        await contexte.close();
    }
    await navigateur.close();

    // Rapport
    const echapper = s => String(s).replace(/\|/g, '\\|');
    const total = passes.reduce((n, p) => n + p.ecrans.length, 0);
    const enDefaut = passes.reduce((n, p) => n + p.ecrans.filter(e => e.problemes.length).length, 0);
    let md = `# Parcours navigateur « ${SERIE} »\n\n`;
    md += `${BASE}, ${new Date().toISOString().slice(0, 16).replace('T', ' ')} UTC. `;
    md += `Modules : ${Object.keys(PREPARATION.modules).join(', ') || 'aucun'}.\n\n`;
    md += `${total} écrans, ${enDefaut} avec au moins un problème.\n`;
    for (const p of passes) {
        md += `\n## Passe ${p.nom} (${p.reglage.largeur} px, thème ${p.reglage.theme === 'dark' ? 'sombre' : 'clair'})\n\n`;
        md += `| Capture | Écran | Problèmes |\n|---|---|---|\n`;
        for (const e of p.ecrans) md += `| [${e.nom}](${p.nom}/${e.nom}.png) | ${echapper(e.libelle)} | ${e.problemes.length || ''} |\n`;
        const details = p.ecrans.filter(e => e.problemes.length);
        if (details.length) {
            md += `\n### Détail\n`;
            for (const e of details) {
                md += `\n**${e.nom}** — ${echapper(e.libelle)}\n\n`;
                for (const m of e.problemes) md += `- ${m}\n`;
            }
        }
    }
    fs.writeFileSync(path.join(DOSSIER, 'rapport.md'), md);
    fs.writeFileSync(path.join(DOSSIER, 'resultats.json'), JSON.stringify({
        serie: SERIE, url: BASE, modules: Object.keys(PREPARATION.modules),
        passes: passes.map(p => ({ nom: p.nom, largeur: p.reglage.largeur, theme: p.reglage.theme, ecrans: p.ecrans })),
    }, null, 1));
    console.log(`\n${total} écrans dans ${path.relative(RACINE, DOSSIER)}/, ${enDefaut} avec problème — rapport.md`);
    process.exit(enDefaut ? 1 : 0);
})().catch(e => { console.error(e.stack || e.message); process.exit(2); });
