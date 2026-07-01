import bpy
import sys
import argparse

def convert_to_glb(input_path: str, output_path: str):
    # Reset scene
    bpy.ops.wm.read_factory_settings(use_empty=True)

    ext = input_path.rsplit('.', 1)[-1].lower()

    if ext == 'blend':
        bpy.ops.wm.open_mainfile(filepath=input_path)
    elif ext == 'fbx':
        bpy.ops.import_scene.fbx(filepath=input_path)
    elif ext == 'obj':
        bpy.ops.wm.obj_import(filepath=input_path)
    else:
        raise ValueError(f"Unsupported format: {ext}")

    # Export ke .glb
    bpy.ops.export_scene.gltf(
        filepath=output_path,
        export_format='GLB',
        export_texcoords=True,
        export_normals=True,
        export_materials='EXPORT',
        export_apply=True,
    )
    print(f"Exported: {output_path}")

if __name__ == '__main__':
    argv = sys.argv
    argv = argv[argv.index('--') + 1:]

    parser = argparse.ArgumentParser()
    parser.add_argument('--input',  required=True)
    parser.add_argument('--output', required=True)
    args = parser.parse_args(argv)

    convert_to_glb(args.input, args.output)