const Ziggy = {"url":"http:\/\/localhost\/package-development\/public","port":null,"defaults":{},"routes":{"cubeta.starter.settings":{"uri":"cubeta-starter\/settings","methods":["GET","HEAD"]},"cubeta.starter.settings.set":{"uri":"cubeta-starter\/settings","methods":["POST"]},"cubeta.starter.add.actor":{"uri":"cubeta-starter\/add-actor","methods":["POST"]},"cubeta.starter.clear.logs":{"uri":"cubeta-starter\/clear-logs","methods":["GET","HEAD"]},"cubeta.starter.generate.page":{"uri":"cubeta-starter","methods":["GET","HEAD"]},"cubeta.starter.generate":{"uri":"cubeta-starter\/generate","methods":["POST"]},"set-locale":{"uri":"locale","methods":["POST"]},"v1.web.public.index":{"uri":"v1\/dashboard","methods":["GET","HEAD"]},"storage.local":{"uri":"storage\/{path}","methods":["GET","HEAD"],"wheres":{"path":".*"},"parameters":["path"]}}};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
  Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export { Ziggy };
