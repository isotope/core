export function extractScript(content) {
    let txt = content;
    let scripts = '';

    txt = txt.replace(/<script([^>]*)>([\s\S]*?)<\/script>/gi, (all, attr, code) => {
        const type = attr.match(/type=['"]?([^"']+)/);

        if (type !== null && type[1] !== 'text/javascript') {
            return all;
        }

        scripts += `${code}\n`;
        return '';
    });

    return [txt, scripts];
}
