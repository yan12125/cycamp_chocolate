// This file should not be executed from browsers
if(typeof exports !== 'undefined' && typeof module !== 'undefined')
{
    var fs = require('fs');
    var data = fs.readFileSync('./data.uncompressed.json');
    try
    {
        var data2 = JSON.parse(data);
    }
    catch(e)
    {
        process.exit(1);
    }
    delete data2.comments; // delete comments
    if(fs.existsSync('./data.json'))
    {
        fs.renameSync('./data.json', './data.json.old');
    }
    fs.writeFileSync('./data.json', JSON.stringify(data2));
}
