#!/usr/bin/env node
/*
    Copyright (c) 2026 Ovensia et contributeurs
    GNU General Public License (GPL) version 2 ou ultérieure

    Rastérise le jeu d'icônes à la taille exacte de chaque image remplacée :
    - administration (correspondances.js) dans templates/backoffice/ploopi3/img/ ;
    - modules et types de fichiers du socle (modules.js) dans install/<module>/files/img/
      et img/mimetypes/.

    Prérequis : Node.js et Playwright avec Chromium (npm install playwright,
    npx playwright install chromium). Le module est cherché dans PLAYWRIGHT_MODULE,
    puis par require('playwright'). ImageMagick (convert) pour les quelques GIF et JPEG.

    Usage, depuis la racine du dépôt :
      node tools/icones/generer.js                    génère toutes les icônes
      node tools/icones/generer.js --seul install/doc  seulement les jeux sous ce chemin
                                                    (plusieurs chemins séparés par des virgules)
      node tools/icones/generer.js --planche f.png     et une planche de contrôle
*/

const fs = require('fs');
const os = require('os');
const path = require('path');
const { execFileSync } = require('child_process');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const { COULEURS, ICONES } = require('./correspondances.js');
const { JEUX } = require('./modules.js');

const RACINE = path.resolve(__dirname, '../..');
const SOURCES = path.join(__dirname, 'tabler');

// Dimensions d'une image PNG, GIF ou JPEG existante (en-têtes du format)
function dimensions(strFichier)
{
    const buf = fs.readFileSync(strFichier);
    if (buf.slice(1, 4).toString() === 'PNG') return [buf.readUInt32BE(16), buf.readUInt32BE(20)];
    if (buf.slice(0, 3).toString() === 'GIF') return [buf.readUInt16LE(6), buf.readUInt16LE(8)];
    if (buf[0] === 0xff && buf[1] === 0xd8) {
        // Segments JPEG jusqu'à l'en-tête de trame (SOF0 à SOF2)
        for (let i = 2; i < buf.length - 9;) {
            const intMarqueur = buf[i + 1];
            if (intMarqueur >= 0xc0 && intMarqueur <= 0xc2) return [buf.readUInt16BE(i + 7), buf.readUInt16BE(i + 5)];
            i += 2 + buf.readUInt16BE(i + 2);
        }
    }
    throw new Error(`${strFichier} : format non reconnu`);
}

function couleur(icone, strCle)
{
    const strCouleur = COULEURS[strCle];
    if (!strCouleur) throw new Error(`${icone.cible} : couleur inconnue ${strCle}`);
    return strCouleur;
}

// Balisage SVG d'une icône, dimensionné et coloré
function svg(icone, intCote)
{
    const strCouleur = couleur(icone, icone.couleur);

    if (icone.pastille) {
        const r = icone.pastille / 2;
        return `<svg width="${intCote}" height="${intCote}" viewBox="0 0 ${intCote} ${intCote}"><circle cx="${intCote / 2}" cy="${intCote / 2}" r="${r}" fill="${strCouleur}"/></svg>`;
    }

    let strSvg = fs.readFileSync(path.join(SOURCES, icone.rempli ? 'filled' : 'outline', `${icone.glyphe}.svg`), 'utf8');
    strSvg = strSvg
        .replace(/width="24"/, `width="${intCote}"`)
        .replace(/height="24"/, `height="${intCote}"`)
        .replace(/stroke-width="2"/, `stroke-width="${icone.trait || 2}"`)
        .replace(/currentColor/g, strCouleur);
    return strSvg;
}

(async () => {
    const intPlanche = process.argv.indexOf('--planche');
    const strPlanche = intPlanche > 0 ? process.argv[intPlanche + 1] : null;
    const intSeul = process.argv.indexOf('--seul');
    const arrSeul = intSeul > 0 ? process.argv[intSeul + 1].split(',').map(s => s.replace(/\/+$/, '')) : [];

    const arrJeux = [{ racine: 'templates/backoffice/ploopi3/img', icones: ICONES }, ...JEUX]
        .filter(jeu => !arrSeul.length || arrSeul.some(s => jeu.racine === s || jeu.racine.startsWith(s + '/')));
    if (!arrJeux.length) throw new Error(`aucun jeu sous ${arrSeul.join(', ')}`);

    const strTemp = fs.mkdtempSync(path.join(os.tmpdir(), 'icones-'));
    const navigateur = await chromium.launch();
    const page = await navigateur.newPage({ deviceScaleFactor: 1 });
    const arrPlanche = [];
    let intNb = 0;

    for (const jeu of arrJeux) {
        for (const icone of jeu.icones) {
            const strFichier = path.join(RACINE, jeu.racine, icone.cible);
            const strFormat = path.extname(icone.cible).slice(1);
            intNb++;

            // Image vectorielle : le glyphe coloré, sans rastérisation
            if (strFormat === 'svg') {
                fs.writeFileSync(strFichier, svg(icone, 24));
                continue;
            }

            const [l, h] = icone.taille || dimensions(strFichier);
            const intCote = Math.round(Math.min(l, h) * (icone.echelle || 1));
            const strJustif = icone.alignement === 'gauche' ? 'flex-start' : 'center';
            const strFond = icone.fond ? couleur(icone, icone.fond) : 'transparent';
            if (strFormat === 'jpg' && !icone.fond) throw new Error(`${icone.cible} : un JPEG demande un fond`);

            await page.setViewportSize({ width: Math.max(l, 32), height: Math.max(h, 32) });
            // flex: none : un glyphe plus grand que l'image (echelle > 1) est rogné, pas réduit
            await page.setContent(`<!doctype html><html><body style="margin:0;background:transparent">
                <style>#c > svg { flex: none; }</style>
                <div id="c" style="width:${l}px;height:${h}px;overflow:hidden;background:${strFond};display:flex;align-items:center;justify-content:${strJustif}">${svg(icone, intCote)}</div>
                </body></html>`);
            fs.mkdirSync(path.dirname(strFichier), { recursive: true });

            if (strFormat === 'png') {
                await page.locator('#c').screenshot({ path: strFichier, omitBackground: !icone.fond });
            } else {
                // GIF : transparence tout ou rien (seuil à mi-opacité) ; JPEG : opaque
                const strPng = path.join(strTemp, 'rendu.png');
                await page.locator('#c').screenshot({ path: strPng, omitBackground: !icone.fond });
                const arrOptions = strFormat === 'gif'
                    ? (icone.fond ? [] : ['-channel', 'A', '-threshold', '50%', '+channel'])
                    : ['-quality', '92'];
                execFileSync('convert', [strPng, ...arrOptions, '-strip', strFichier]);
            }

            const strMime = { png: 'image/png', gif: 'image/gif', jpg: 'image/jpeg' }[strFormat];
            arrPlanche.push({
                jeu: jeu.racine, cible: icone.cible, l, h, sombre: icone.couleur === 'blanc',
                src: `data:${strMime};base64,${fs.readFileSync(strFichier).toString('base64')}`
            });
        }
    }

    if (strPlanche) {
        const strCases = arrJeux.map(jeu => {
            const arrCases = arrPlanche.filter(c => c.jeu === jeu.racine);
            if (!arrCases.length) return '';
            return `<h3>${jeu.racine}/ — ${arrCases.length} images</h3><div class="g">` + arrCases.map(c =>
                `<div class="case"><div class="img${c.sombre ? ' sombre' : ''}"><img src="${c.src}" width="${Math.min(c.l, 48)}"></div><span>${c.cible}</span></div>`
            ).join('') + '</div>';
        }).join('');
        await page.setViewportSize({ width: 1200, height: 800 });
        await page.setContent(`<!doctype html><html><body style="margin:16px;font:11px sans-serif;color:#1f2a30;background:#fff">
            <style>.g{display:grid;grid-template-columns:repeat(5,1fr);gap:6px}.case{display:flex;align-items:center;gap:8px;border:1px solid #dae1e7;border-radius:4px;padding:6px}
            .img{width:52px;height:30px;display:flex;align-items:center;justify-content:center;background:#f6f8fa}.sombre{background:#1b3f4a}span{word-break:break-all}h3{margin:18px 0 8px}</style>
            ${strCases}</body></html>`);
        await page.screenshot({ path: strPlanche, fullPage: true });
    }

    await navigateur.close();
    fs.rmSync(strTemp, { recursive: true, force: true });
    console.log(`${intNb} icône(s) écrite(s) dans ${arrJeux.length} jeu(x)${strPlanche ? ', planche : ' + strPlanche : ''}`);
})().catch(e => { console.error(e.message); process.exit(1); });
