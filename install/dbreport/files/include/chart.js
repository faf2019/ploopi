/*
    Copyright (c) 2007-2026 Ovensia
    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi.  If not, see <https://www.gnu.org/licenses/>.
*/

/**
 * Rendu des graphiques de requête avec Chart.js (MIT), à partir de la configuration
 * produite par dbreport_query::displayChart().
 *
 * Les paramètres enregistrés avant le passage à Chart.js (types, formats d'infobulle et
 * d'indices à la manière de Highcharts : {series.name}, {point.y:.0f}, {percentage:.2f}…)
 * sont repris tels quels.
 */

var dbreport_chart = (function() {

    /**
     * Nombre formaté : décimales fixes si demandées, milliers séparés par une espace
     */
    function formatNumber(value, decimals) {
        if (value === null || value === undefined || isNaN(value)) return '';
        var str = (decimals === null) ? String(value) : Number(value).toFixed(decimals);
        var parts = str.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        return parts.join('.');
    }

    /**
     * Gabarit « {chemin} » ou « {chemin:.Nf} » appliqué à un contexte de point
     */
    function applyFormat(format, context) {
        return String(format).replace(/\{([a-zA-Z_.]+)(?::\.(\d+)f)?\}/g, function(match, path, decimals) {
            var value = context;
            var keys = path.split('.');
            for (var i = 0; i < keys.length; i++) {
                if (value === null || typeof value !== 'object' || !(keys[i] in value)) return '';
                value = value[keys[i]];
            }
            if (typeof value === 'number') return formatNumber(value, decimals === undefined ? null : parseInt(decimals, 10));
            return value === null || value === undefined ? '' : String(value);
        }).replace(/<br\s*\/?>/gi, '\n').replace(/<[^>]*>/g, '').replace(/\n+$/, '');
    }

    function colorAt(colors, index) {
        return colors.length ? colors[index % colors.length] : '#888888';
    }

    function withOpacity(color, opacity) {
        var m = /^rgba?\((\d+),\s*(\d+),\s*(\d+)/.exec(color);
        if (m) return 'rgba(' + m[1] + ',' + m[2] + ',' + m[3] + ',' + opacity + ')';
        m = /^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(color);
        if (m) return 'rgba(' + parseInt(m[1], 16) + ',' + parseInt(m[2], 16) + ',' + parseInt(m[3], 16) + ',' + opacity + ')';
        return color;
    }

    /**
     * Fond du graphique (Chart.js laisse le canevas transparent)
     */
    var pluginBackground = {
        id: 'dbreportBackground',
        beforeDraw: function(chart, args, opts) {
            if (!opts.color) return;
            var ctx = chart.ctx;
            ctx.save();
            ctx.fillStyle = opts.color;
            ctx.fillRect(0, 0, chart.width, chart.height);
            ctx.restore();
        }
    };

    /**
     * Intervalles : une bande sur deux colorée, le long de chaque axe, sous la grille
     */
    var pluginBands = {
        id: 'dbreportBands',
        beforeDraw: function(chart, args, opts) {
            if (!opts.display) return;
            var area = chart.chartArea, ctx = chart.ctx;
            ctx.save();
            ctx.beginPath();
            ctx.rect(area.left, area.top, area.right - area.left, area.bottom - area.top);
            ctx.clip();
            [['indexAxis', opts.indexColor], ['valueAxis', opts.valueColor]].forEach(function(item) {
                var scale = chart.scales[opts[item[0]]];
                if (!scale || !item[1]) return;
                var horizontal = scale.isHorizontal();
                var ticks = scale.ticks.length;
                ctx.fillStyle = item[1];
                for (var i = 0; i < ticks; i += 2) {
                    var a, b;
                    if (scale.type === 'category') {
                        // une bande par catégorie, centrée sur son repère
                        var half = ticks > 1 ? Math.abs(scale.getPixelForTick(1) - scale.getPixelForTick(0)) / 2 : (horizontal ? area.right - area.left : area.bottom - area.top) / 2;
                        a = scale.getPixelForTick(i) - half;
                        b = scale.getPixelForTick(i) + half;
                    }
                    else {
                        if (i + 1 >= ticks) break;
                        a = scale.getPixelForTick(i);
                        b = scale.getPixelForTick(i + 1);
                    }
                    if (horizontal) ctx.fillRect(Math.min(a, b), area.top, Math.abs(b - a), area.bottom - area.top);
                    else ctx.fillRect(area.left, Math.min(a, b), area.right - area.left, Math.abs(b - a));
                }
            });
            ctx.restore();
        }
    };

    /**
     * Indices : valeur affichée à côté de chaque point
     */
    function pluginIndexes(context) {
        return {
            id: 'dbreportIndexes',
            afterDatasetsDraw: function(chart, args, opts) {
                if (!opts.display) return;
                var ctx = chart.ctx;
                ctx.save();
                ctx.font = opts.fontSize + 'px ' + (opts.fontFamily || Chart.defaults.font.family);
                ctx.fillStyle = opts.color;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                chart.data.datasets.forEach(function(dataset, datasetIndex) {
                    var meta = chart.getDatasetMeta(datasetIndex);
                    if (meta.hidden) return;
                    meta.data.forEach(function(element, index) {
                        if (dataset.data[index] === null || dataset.data[index] === undefined) return;
                        var pos = element.tooltipPosition(true);
                        var lines = applyFormat(opts.format, context(chart, datasetIndex, index)).split('\n');
                        ctx.save();
                        ctx.translate(pos.x + opts.x, pos.y + opts.y - 2);
                        if (opts.rotation) ctx.rotate(opts.rotation * Math.PI / 180);
                        for (var l = 0; l < lines.length; l++) ctx.fillText(lines[l], 0, (l - lines.length + 1) * opts.fontSize * 1.2);
                        ctx.restore();
                    });
                });
                ctx.restore();
            }
        };
    }

    /**
     * Rendu dans le conteneur cfg.container
     */
    function render(cfg) {
        var type = cfg.type;
        var isPie = (type === 'pie' || type === 'doughnut');
        var isHorizontal = /bar/i.test(type);
        var isPercent = /100$/.test(type);
        var isStacked = /^stacked/.test(type);
        var isArea = /area/i.test(type);
        var isCurve = /spline/i.test(type);
        var isLine = !isPie && !/bar|column/i.test(type);

        Chart.defaults.font.family = cfg.font || Chart.defaults.font.family;
        Chart.defaults.color = cfg.axis.fontColor;

        var raw = cfg.series.map(function(serie) { return serie.data; });

        // Totaux par catégorie (empilements, pourcentages) et par série (secteurs)
        var totals = cfg.categories.map(function(cat, i) {
            return raw.reduce(function(sum, data) { return sum + (data[i] || 0); }, 0);
        });
        var serieTotals = raw.map(function(data) {
            return data.reduce(function(sum, v) { return sum + (v || 0); }, 0);
        });

        function context(chart, datasetIndex, index) {
            var y = raw[datasetIndex][index];
            var total = isPie ? serieTotals[datasetIndex] : totals[index];
            var color = isPie ? colorAt(cfg.colors, index) : colorAt(cfg.colors, datasetIndex);
            var percentage = total ? y * 100 / total : 0;
            var point = { x: cfg.categories[index], y: y, name: cfg.categories[index], category: cfg.categories[index], color: color, total: total, percentage: percentage, key: cfg.categories[index] };
            return { x: point.x, y: y, key: point.key, total: total, percentage: percentage, point: point, series: { name: cfg.series[datasetIndex].name, color: color } };
        }

        var datasets = cfg.series.map(function(serie, s) {
            var data = serie.data.slice();
            if (isPercent) data = data.map(function(v, i) { return totals[i] ? (v || 0) * 100 / totals[i] : 0; });

            var dataset = { label: serie.name, data: data };
            if (isPie) {
                dataset.backgroundColor = cfg.categories.map(function(c, i) { return colorAt(cfg.colors, i); });
                dataset.borderColor = '#ffffff';
                dataset.borderWidth = 1;
            }
            else if (isLine) {
                dataset.borderColor = colorAt(cfg.colors, s);
                dataset.backgroundColor = isArea ? withOpacity(colorAt(cfg.colors, s), 0.5) : colorAt(cfg.colors, s);
                dataset.borderWidth = cfg.lineWidth;
                dataset.tension = isCurve ? 0.4 : 0;
                dataset.fill = isArea ? (isStacked && s > 0 ? '-1' : 'origin') : false;
                dataset.pointRadius = 3;
                dataset.spanGaps = true;
            }
            else {
                dataset.backgroundColor = colorAt(cfg.colors, s);
                dataset.borderWidth = 0;
            }
            return dataset;
        });

        var legendPosition = cfg.legend.valign === 'center' ? (cfg.legend.align === 'left' ? 'left' : 'right') : cfg.legend.valign;
        var legendAlign = { left: 'start', center: 'center', right: 'end' }[cfg.legend.align] || 'center';

        var options = {
            responsive: true,
            maintainAspectRatio: false,
            animation: cfg.animation ? {} : false,
            interaction: { mode: isPie ? 'nearest' : 'index', intersect: isPie },
            plugins: {
                title: { display: cfg.title.text !== '', text: cfg.title.text, color: cfg.title.color, font: { size: cfg.title.size } },
                subtitle: { display: cfg.subtitle.text !== '', text: cfg.subtitle.text, color: cfg.title.color, font: { size: Math.max(9, Math.round(cfg.title.size / 2)) } },
                legend: {
                    display: cfg.legend.display,
                    position: legendPosition,
                    align: legendAlign,
                    labels: { color: cfg.legend.color, font: { size: cfg.legend.size } }
                },
                tooltip: {
                    callbacks: {
                        label: function(item) {
                            return applyFormat(cfg.tooltip, context(item.chart, item.datasetIndex, item.dataIndex)).split('\n');
                        }
                    }
                },
                dbreportBackground: { color: cfg.background },
                dbreportBands: { display: !isPie && cfg.bands.display, indexAxis: isHorizontal ? 'y' : 'x', valueAxis: isHorizontal ? 'x' : 'y', indexColor: cfg.bands.xColor, valueColor: cfg.bands.yColor },
                dbreportIndexes: { display: cfg.indexes.display, format: cfg.indexes.format, color: cfg.indexes.color, fontSize: cfg.indexes.size, fontFamily: cfg.font, rotation: cfg.indexes.rotation, x: cfg.indexes.x, y: cfg.indexes.y }
            }
        };

        if (cfg.tooltip === '') delete options.plugins.tooltip.callbacks;

        if (isPie) {
            if (type === 'doughnut') options.cutout = '50%';
        }
        else {
            var axis = function(def, isIndex) {
                return {
                    stacked: isStacked,
                    beginAtZero: !isIndex,
                    max: (!isIndex && isPercent) ? 100 : undefined,
                    title: { display: def.title !== '', text: def.title, color: cfg.axis.fontColor, font: { size: cfg.axis.fontSize } },
                    ticks: {
                        color: cfg.axis.fontColor,
                        font: { size: cfg.axis.fontSize },
                        callback: function(value) {
                            var label = isIndex ? this.getLabelForValue(value) : formatNumber(value, null);
                            return def.prefix + label + def.suffix;
                        }
                    },
                    grid: { display: def.gridWidth > 0, color: cfg.axis.gridColor, lineWidth: def.gridWidth, drawTicks: def.lineWidth > 0, tickColor: cfg.axis.color },
                    border: { display: def.lineWidth > 0, color: cfg.axis.color, width: def.lineWidth }
                };
            };
            // x : les catégories (axe vertical pour les barres), y : les valeurs
            options.indexAxis = isHorizontal ? 'y' : 'x';
            options.scales = {};
            options.scales[isHorizontal ? 'y' : 'x'] = axis(cfg.axis.x, true);
            options.scales[isHorizontal ? 'x' : 'y'] = axis(cfg.axis.y, false);
        }

        var canvas = document.createElement('canvas');
        document.getElementById(cfg.container).appendChild(canvas);

        return new Chart(canvas, {
            type: isPie ? type : (isLine ? 'line' : 'bar'),
            data: { labels: cfg.categories, datasets: datasets },
            options: options,
            plugins: [pluginBackground, pluginBands, pluginIndexes(context)]
        });
    }

    /**
     * Téléchargement du graphique en PNG
     */
    function download(chart, filename) {
        var link = document.createElement('a');
        link.href = chart.toBase64Image('image/png', 1);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    return { render: render, download: download, applyFormat: applyFormat };

})();
